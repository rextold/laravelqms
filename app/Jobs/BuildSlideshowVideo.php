<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use App\Models\Video;

class BuildSlideshowVideo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $organizationId;
    public $title;
    public $imageFiles; // array of full paths
    public $audioFile; // nullable full path
    public $duration;

    public function __construct($organizationId, $title, array $imageFiles, $audioFile = null, $duration = 5)
    {
        $this->organizationId = $organizationId;
        $this->title = $title;
        $this->imageFiles = $imageFiles;
        $this->audioFile = $audioFile;
        $this->duration = $duration;
    }

    public function handle()
    {
        $ffmpeg = trim(shell_exec('which ffmpeg 2>/dev/null')) ?: 'ffmpeg';

        $tmpBase = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'makevideo_' . $this->organizationId . '_' . uniqid();
        @mkdir($tmpBase, 0775, true);

        try {
            // copy images to tmpBase with safe names
            $segmentFiles = [];
            foreach ($this->imageFiles as $idx => $imgPath) {
                $target = $tmpBase . DIRECTORY_SEPARATOR . 'img_' . $idx . '_' . basename($imgPath);
                copy($imgPath, $target);
                $seg = $tmpBase . DIRECTORY_SEPARATOR . 'seg_' . $idx . '.mp4';
                $cmd = sprintf('%s -y -loop 1 -i %s -c:v libx264 -t %s -pix_fmt yuv420p -vf "scale=1280:-2" -r 25 %s 2>&1',
                    escapeshellcmd($ffmpeg),
                    escapeshellarg($target),
                    escapeshellarg($this->duration),
                    escapeshellarg($seg)
                );
                $out = []; $rc = 0; exec($cmd, $out, $rc);
                if ($rc !== 0 || !file_exists($seg)) {
                    Log::error('BuildSlideshowVideo: failed creating segment', ['cmd' => $cmd, 'out' => $out]);
                    throw new \Exception('Failed to create segment for ' . $imgPath);
                }
                $segmentFiles[] = $seg;
            }

            $listFile = $tmpBase . DIRECTORY_SEPARATOR . 'list.txt';
            $fh = fopen($listFile, 'w');
            foreach ($segmentFiles as $seg) {
                fwrite($fh, "file '" . str_replace("'","'\\''", $seg) . "'\n");
            }
            fclose($fh);

            $concatOut = $tmpBase . DIRECTORY_SEPARATOR . 'out_concat.mp4';
            $cmdConcat = sprintf('%s -y -f concat -safe 0 -i %s -c copy %s 2>&1',
                escapeshellcmd($ffmpeg),
                escapeshellarg($listFile),
                escapeshellarg($concatOut)
            );
            $out = []; $rc = 0; exec($cmdConcat, $out, $rc);
            if ($rc !== 0 || !file_exists($concatOut)) {
                Log::error('BuildSlideshowVideo: concat failed', ['cmd' => $cmdConcat, 'out' => $out]);
                throw new \Exception('Failed concatenating segments');
            }

            $finalName = uniqid('makevideo_') . '.mp4';
            $finalRelative = 'videos/' . $finalName;
            $finalPath = storage_path('app/public/' . $finalRelative);

            if ($this->audioFile && file_exists($this->audioFile)) {
                $cmdMerge = sprintf('%s -y -i %s -i %s -c:v copy -c:a aac -shortest %s 2>&1',
                    escapeshellcmd($ffmpeg),
                    escapeshellarg($concatOut),
                    escapeshellarg($this->audioFile),
                    escapeshellarg($finalPath)
                );
                $out = []; $rc = 0; exec($cmdMerge, $out, $rc);
                if ($rc !== 0 || !file_exists($finalPath)) {
                    Log::error('BuildSlideshowVideo: merge failed', ['cmd' => $cmdMerge, 'out' => $out]);
                    throw new \Exception('Failed merging audio');
                }
            } else {
                if (!@rename($concatOut, $finalPath)) {
                    if (!@copy($concatOut, $finalPath)) {
                        throw new \Exception('Failed storing final video');
                    }
                }
            }

            // Create Video record
            $video = Video::create([
                'title' => $this->title,
                'video_type' => 'file',
                'file_path' => $finalRelative,
                'order' => Video::where('organization_id', $this->organizationId)->max('order') + 1,
                'is_active' => true,
                'organization_id' => $this->organizationId,
            ]);

            // cleanup
            foreach (glob($tmpBase . DIRECTORY_SEPARATOR . '*') as $f) @unlink($f);
            @rmdir($tmpBase);

            return $video;
        } catch (\Throwable $e) {
            Log::error('BuildSlideshowVideo failed', ['error' => $e->getMessage()]);
            // cleanup
            foreach (glob($tmpBase . DIRECTORY_SEPARATOR . '*') as $f) @unlink($f);
            @rmdir($tmpBase);
            throw $e;
        }
    }
}
