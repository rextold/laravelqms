<?php

namespace App\Console\Commands;

use App\Models\OrganizationSetting;
use App\Models\Queue;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Full midnight reset for all (or a specific) organization:
 *   1. Mark all active/waiting queues as completed  → clears Now Serving & Waiting list
 *   2. Set all counter users offline + null session  → forces re-login on next open
 *   3. Delete counter session files                  → browser gets 401 on next poll
 *   4. Reset queue sequence counters                 → first ticket of the day = 0001
 *
 * Runs automatically at midnight via the scheduler (see bootstrap/app.php).
 * Can also be run manually:
 *   php artisan queue:reset-daily              — resets all organizations
 *   php artisan queue:reset-daily --org=5      — resets org with ID 5 only
 */
class ResetDailyQueues extends Command
{
    protected $signature = 'queue:reset-daily
                            {--org= : Organization ID to reset (omit for all organizations)}
                            {--dry-run : Show what would be done without making any changes}';

    protected $description = 'Midnight reset: clear queues, sign out counters, reset sequences';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $orgId  = $this->option('org') ? (int) $this->option('org') : null;

        // ── 1. Clear ALL active queues (today's included) ────────────────────
        //     This empties the Now Serving and Waiting list on the monitor.
        $activeQuery = Queue::whereIn('status', ['waiting', 'called', 'serving']);

        if ($orgId !== null) {
            $activeQuery->where('organization_id', $orgId);
        }

        $activeCount = $activeQuery->count();

        if ($activeCount > 0) {
            $this->info("[Queues] Found {$activeCount} active queue(s) to clear.");
            if (!$dryRun) {
                $activeQuery->update([
                    'status'       => 'completed',
                    'completed_at' => now(),
                ]);
                $this->info("[Queues] Marked {$activeCount} queue(s) as completed.");
            } else {
                $this->warn("[DRY RUN] Would mark {$activeCount} queue(s) as completed.");
            }
        } else {
            $this->info('[Queues] No active queues to clear.');
        }

        // ── 2. Log out all counter users ──────────────────────────────────────
        //     Set is_online = false and clear session_id so the browser is
        //     redirected to login on the next request.
        $counterQuery = User::where('role', 'counter');

        if ($orgId !== null) {
            $counterQuery->where('organization_id', $orgId);
        }

        $counters = $counterQuery->get(['id', 'username', 'session_id']);

        $loggedOutCount = 0;
        foreach ($counters as $counter) {
            if (!$dryRun) {
                // Delete the server-side session file so the browser tab gets
                // a fresh unauthenticated state on its next HTTP request.
                if ($counter->session_id) {
                    $this->deleteSessionFile($counter->session_id);
                }

                $counter->update([
                    'is_online'  => false,
                    'session_id' => null,
                ]);
                $loggedOutCount++;
            } else {
                $this->warn("[DRY RUN] Would log out counter: {$counter->username}");
            }
        }

        if (!$dryRun) {
            $this->info("[Counters] Logged out {$loggedOutCount} counter user(s) and cleared their sessions.");
        }

        // ── 3. Reset sequence counters ────────────────────────────────────────
        $settingsQuery = OrganizationSetting::query();
        if ($orgId !== null) {
            $settingsQuery->where('organization_id', $orgId);
        }

        $settings = $settingsQuery->get();

        if ($settings->isEmpty()) {
            $this->warn('No organization settings found to reset.');
            return self::SUCCESS;
        }

        foreach ($settings as $setting) {
            $label = "Org #{$setting->organization_id}";

            if (!$dryRun) {
                DB::table('organization_settings')
                    ->where('id', $setting->id)
                    ->update([
                        'last_queue_sequence' => 0,
                        'last_queue_date'     => null,
                    ]);
                $this->info("[{$label}] Sequence reset. First ticket today will be 0001.");
            } else {
                $this->warn("[DRY RUN] [{$label}] Would reset sequence from {$setting->last_queue_sequence} to 0.");
            }
        }

        $this->info('Daily reset complete.');

        return self::SUCCESS;
    }

    /**
     * Delete a session file to immediately invalidate the counter's browser session.
     * The session driver is "file" (see config/session.php).
     */
    private function deleteSessionFile(string $sessionId): void
    {
        try {
            $path = config('session.files', storage_path('framework/sessions'))
                . DIRECTORY_SEPARATOR . $sessionId;

            if (file_exists($path)) {
                unlink($path);
            }
        } catch (\Throwable $e) {
            Log::warning('[ResetDailyQueues] Could not delete session file for session ' . $sessionId . ': ' . $e->getMessage());
        }
    }
}
