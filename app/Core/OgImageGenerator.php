<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Dynamic Per-Cheat Social OpenGraph Thumbnail Generator (GD)
 * Renders 1200x630 editorial ViceCodes cards over vice-hero.jpg and caches in public/og-cache/.
 */
final class OgImageGenerator
{
    public static function cachePathForCheat(string $slug): string
    {
        return dirname(__DIR__, 2) . '/public/og-cache/cheat-' . preg_replace('/[^a-z0-9\-]/', '', $slug) . '.png';
    }

    public static function invalidateCheat(string $slug): void
    {
        $file = self::cachePathForCheat($slug);
        if (file_exists($file)) {
            @unlink($file);
        }
    }

    public static function generateForCheat(array $cheat): ?string
    {
        if (!extension_loaded('gd')) {
            return null;
        }

        $slug = (string) ($cheat['slug'] ?? 'cheat');
        $outFile = self::cachePathForCheat($slug);
        $outDir = dirname($outFile);
        if (!is_dir($outDir)) {
            @mkdir($outDir, 0755, true);
        }

        $updatedAt = strtotime((string) ($cheat['updated_at'] ?? 'now')) ?: time();
        if (file_exists($outFile) && (int) @filemtime($outFile) >= $updatedAt) {
            return $outFile;
        }

        $w = 1200;
        $h = 630;
        $canvas = imagecreatetruecolor($w, $h);
        if (!$canvas) {
            return null;
        }

        // Palette matching Vice Codes design tokens
        $ink       = imagecolorallocate($canvas, 27, 28, 29);     // #1B1C1D
        $paper     = imagecolorallocate($canvas, 250, 246, 240);  // #FAF6F0
        $paperWarm = imagecolorallocate($canvas, 246, 241, 231);  // #F6F1E7
        $surface   = imagecolorallocate($canvas, 238, 232, 223);  // #EEE8DF
        $crimson   = imagecolorallocate($canvas, 176, 40, 31);    // #B0281F
        $coral     = imagecolorallocate($canvas, 239, 90, 79);    // #EF5A4F
        $gold      = imagecolorallocate($canvas, 184, 146, 42);   // #B8922A
        $muted     = imagecolorallocate($canvas, 216, 208, 200);  // #D8D0C8

        imagefill($canvas, 0, 0, $ink);

        // Background: vice-hero.jpg cropped & desaturated
        $heroPath = dirname(__DIR__, 2) . '/public/assets/img/vice-hero.jpg';
        if (file_exists($heroPath)) {
            $bg = @imagecreatefromjpeg($heroPath);
            if ($bg) {
                $bw = imagesx($bg);
                $bh = imagesy($bg);
                $scale = max($w / $bw, $h / $bh);
                $nw = (int) round($bw * $scale);
                $nh = (int) round($bh * $scale);
                $dx = (int) round(($w - $nw) / 2);
                $dy = (int) round(($h - $nh) / 2);
                imagecopyresampled($canvas, $bg, $dx, $dy, 0, 0, $nw, $nh, $bw, $bh);
                imagedestroy($bg);
                imagefilter($canvas, IMG_FILTER_GRAYSCALE);
                imagefilter($canvas, IMG_FILTER_CONTRAST, -15);
            }
        }

        // Dark editorial scrim overlay
        $scrim = imagecolorallocatealpha($canvas, 27, 28, 29, 26);
        imagefilledrectangle($canvas, 0, 0, $w, $h, $scrim);

        // Editorial 3-stripe bar at top (Gold / Coral / Crimson)
        imagefilledrectangle($canvas, 0, 0, 400, 10, $gold);
        imagefilledrectangle($canvas, 400, 0, 800, 10, $coral);
        imagefilledrectangle($canvas, 800, 0, 1200, 10, $crimson);

        // Frame border
        imagerectangle($canvas, 34, 34, $w - 35, $h - 35, $gold);

        $boldFont = self::resolveFont([
            '/System/Library/Fonts/Supplemental/DIN Condensed Bold.ttf',
            '/System/Library/Fonts/Supplemental/Impact.ttf',
            '/System/Library/Fonts/Supplemental/Arial Bold.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
        ]);
        $bodyFont = self::resolveFont([
            '/System/Library/Fonts/Supplemental/Arial.ttf',
            '/System/Library/Fonts/Supplemental/Verdana.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
        ]);

        // Status Stamp Badge
        $status = (string) ($cheat['status'] ?? 'coming_soon');
        $statusLabel = match ($status) {
            'verified'    => 'VERIFIED CHEAT',
            'unconfirmed' => 'UNCONFIRMED',
            'debunked'    => 'DEBUNKED RUMOR',
            default       => 'COMING AT LAUNCH',
        };
        $badgeBg = match ($status) {
            'verified'    => $gold,
            'debunked'    => $crimson,
            'unconfirmed' => $coral,
            default       => $surface,
        };
        $badgeFg = $status === 'debunked' ? $paper : $ink;

        imagefilledrectangle($canvas, 70, 74, 380, 122, $badgeBg);
        self::drawText($canvas, $boldFont, 22, 86, 109, $badgeFg, $statusLabel);

        // Patch badge
        $patch = trim((string) ($cheat['game_patch'] ?? 'v1.00')) ?: 'v1.00';
        imagefilledrectangle($canvas, 400, 74, 590, 122, $ink);
        imagerectangle($canvas, 400, 74, 590, 122, $muted);
        self::drawText($canvas, $boldFont, 21, 416, 109, $muted, 'PATCH ' . strtoupper($patch));

        // Cheat Title (wrapped cleanly)
        $title = strtoupper(trim((string) ($cheat['title'] ?? 'GTA 6 CHEAT CODE')));
        $lines = self::wrapLines($title, 26, 2);
        $y = 228;
        foreach ($lines as $line) {
            self::drawText($canvas, $boldFont, 58, 70, $y, $paperWarm, $line);
            $y += 74;
        }

        // Short Description
        $desc = trim((string) ($cheat['short_description'] ?? 'Verified GTA 6 cheat code for PS5, Xbox Series X|S, and PC.'));
        $descLines = self::wrapLines($desc, 62, 2);
        $dy = $y + 18;
        foreach ($descLines as $dLine) {
            self::drawText($canvas, $bodyFont, 20, 70, $dy, $muted, $dLine);
            $dy += 34;
        }

        // Bottom Platform Chips & Brand Stamp
        $platforms = $cheat['platforms'] ?? [];
        $px = 70;
        foreach (['ps5' => 'PS5 DUALSENSE', 'xbox' => 'XBOX SERIES X|S', 'pc' => 'PC'] as $k => $lbl) {
            if (!isset($platforms[$k]) && $k !== 'ps5' && $k !== 'xbox') {
                continue;
            }
            $chipW = strlen($lbl) * 14 + 34;
            imagefilledrectangle($canvas, $px, 505, $px + $chipW, 555, $paper);
            self::drawText($canvas, $boldFont, 20, $px + 16, 540, $ink, $lbl);
            $px += $chipW + 16;
        }

        // Brand footer mark right-aligned
        self::drawText($canvas, $boldFont, 30, $w - 340, 545, $gold, 'VICECODES // GTA 6');

        imagepng($canvas, $outFile);
        imagedestroy($canvas);

        return file_exists($outFile) ? $outFile : null;
    }

    private static function resolveFont(array $candidates): ?string
    {
        foreach ($candidates as $path) {
            if (is_file($path)) {
                return $path;
            }
        }
        return null;
    }

    private static function drawText($img, ?string $font, int $size, int $x, int $y, int $color, string $text): void
    {
        if ($font !== null && function_exists('imagettftext')) {
            @imagettftext($img, $size, 0, $x, $y, $color, $font, $text);
        } else {
            imagestring($img, 5, $x, max(4, $y - 16), $text, $color);
        }
    }

    private static function wrapLines(string $text, int $maxChars, int $maxLines): array
    {
        $wrapped = wordwrap($text, $maxChars, "\n", true);
        $parts = explode("\n", $wrapped);
        if (count($parts) > $maxLines) {
            $parts = array_slice($parts, 0, $maxLines);
            $parts[$maxLines - 1] = rtrim(substr($parts[$maxLines - 1], 0, max(3, $maxChars - 3))) . '...';
        }
        return $parts;
    }
}
