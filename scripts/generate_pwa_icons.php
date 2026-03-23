<?php
/**
 * PWA Icon Generator for Queue Management System
 * Generates all required PNG icons using PHP GD library.
 *
 * Usage: php scripts/generate_pwa_icons.php
 */

if (!extension_loaded('gd')) {
    die("ERROR: PHP GD extension is required. Enable it in php.ini.\n");
}

$outputDir = __DIR__ . '/../public/icons/';

if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

// Brand colours
$brandGrad1 = [102, 126, 234]; // #667eea  (top)
$brandGrad2 = [118,  75, 162]; // #764ba2  (bottom)
$kioskColor = [ 52, 168, 100]; // #34a864
$monitorColor = [ 56, 152, 234]; // #3898ea

// ─────────────────────────────────────────────────────────────
// Helper: draw a vertical-gradient rounded-rectangle icon
// ─────────────────────────────────────────────────────────────
function makeIcon(int $size, string $label, array $grad1, array $grad2): GdImage
{
    $img = imagecreatetruecolor($size, $size);
    imagesavealpha($img, true);
    imagealphablending($img, false);

    // Transparent background
    $transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
    imagefill($img, 0, 0, $transparent);
    imagealphablending($img, true);

    // Rounded-rectangle radius (~18 % of size, minimum 4 px)
    $radius = max(4, (int) round($size * 0.18));

    // Paint gradient line by line, clipping to the rounded-rect region
    for ($y = 0; $y < $size; $y++) {
        $t = ($size > 1) ? $y / ($size - 1) : 0;
        $r = (int) ($grad1[0] + ($grad2[0] - $grad1[0]) * $t);
        $g = (int) ($grad1[1] + ($grad2[1] - $grad1[1]) * $t);
        $b = (int) ($grad1[2] + ($grad2[2] - $grad1[2]) * $t);
        $c = imagecolorallocate($img, $r, $g, $b);

        // Horizontal clipping based on row position (rounded corners)
        $xMin = 0;
        $xMax = $size - 1;

        if ($y < $radius) {
            // Top rounded corners
            $dy   = $radius - $y;
            $clip = $radius - (int) sqrt(max(0, $radius * $radius - $dy * $dy));
            $xMin = $clip;
            $xMax = $size - 1 - $clip;
        } elseif ($y >= $size - $radius) {
            // Bottom rounded corners
            $dy   = $y - ($size - 1 - $radius);
            $clip = $radius - (int) sqrt(max(0, $radius * $radius - $dy * $dy));
            $xMin = $clip;
            $xMax = $size - 1 - $clip;
        }

        if ($xMax >= $xMin) {
            imageline($img, $xMin, $y, $xMax, $y, $c);
        }
    }

    // ──────────────────────────────────────────────────────────
    // Draw label letter(s) centred, scaled to ~45 % of icon size
    // We use GD's built-in font 5 (8×13 px) and tile multiple
    // copies to simulate a larger glyph.
    // ──────────────────────────────────────────────────────────
    $white = imagecolorallocate($img, 255, 255, 255);

    // Desired capital height in pixels
    $targetH = max(8, (int) round($size * 0.45));
    $baseH   = imagefontheight(5);   // 13 px
    $baseW   = imagefontwidth(5);    // 8  px  (per char)
    $scale   = max(1, (int) round($targetH / $baseH));

    $chars    = mb_str_split($label);
    $totalW   = $baseW * count($chars) * $scale;
    $totalH   = $baseH * $scale;
    $startX   = (int) round(($size - $totalW) / 2);
    $startY   = (int) round(($size - $totalH) / 2);

    // Render each pixel of the built-in font scaled up
    // We achieve this by drawing a filled rectangle for each
    // "on" pixel in the source glyph.
    foreach ($chars as $ci => $ch) {
        $charOffX = $startX + $ci * $baseW * $scale;

        // Sample every pixel of the glyph using a 1-px temporary canvas
        $tmp = imagecreatetruecolor($baseW, $baseH);
        $black = imagecolorallocate($tmp, 0, 0, 0);
        $wt    = imagecolorallocate($tmp, 255, 255, 255);
        imagefill($tmp, 0, 0, $black);
        imagestring($tmp, 5, 0, 0, $ch, $wt);

        for ($gy = 0; $gy < $baseH; $gy++) {
            for ($gx = 0; $gx < $baseW; $gx++) {
                $px = imagecolorat($tmp, $gx, $gy);
                $pr = ($px >> 16) & 0xFF;
                if ($pr > 127) {
                    // "on" pixel — draw a scaled rectangle
                    $dx = $charOffX + $gx * $scale;
                    $dy = $startY   + $gy * $scale;
                    imagefilledrectangle(
                        $img,
                        $dx, $dy,
                        $dx + $scale - 1, $dy + $scale - 1,
                        $white
                    );
                }
            }
        }
        imagedestroy($tmp);
    }

    return $img;
}

// ─────────────────────────────────────────────────────────────
// Generate main app icons
// ─────────────────────────────────────────────────────────────
$sizes = [72, 96, 128, 144, 152, 192, 384, 512];

foreach ($sizes as $size) {
    $img  = makeIcon($size, 'Q', $brandGrad1, $brandGrad2);
    $path = $outputDir . "icon-{$size}x{$size}.png";
    imagepng($img, $path, 9);
    imagedestroy($img);
    echo "Generated: icon-{$size}x{$size}.png\n";
}

// ─────────────────────────────────────────────────────────────
// Generate shortcut icons
// ─────────────────────────────────────────────────────────────
$shortcuts = [
    'kiosk'   => ['label' => 'K', 'g1' => $kioskColor,   'g2' => [34, 120, 70]],
    'monitor' => ['label' => 'M', 'g1' => $monitorColor, 'g2' => [30, 100, 180]],
];

foreach ($shortcuts as $name => $cfg) {
    $img  = makeIcon(96, $cfg['label'], $cfg['g1'], $cfg['g2']);
    $path = $outputDir . "{$name}-96x96.png";
    imagepng($img, $path, 9);
    imagedestroy($img);
    echo "Generated: {$name}-96x96.png\n";
}

echo "\nAll PWA icons generated successfully in {$outputDir}\n";
