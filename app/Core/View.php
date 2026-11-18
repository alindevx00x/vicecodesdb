<?php
declare(strict_types=1);

namespace App\Core;

final class View
{
    public static function render(string $template, array $data = [], ?string $layout = 'layouts/main'): void
    {
        $viewsPath = dirname(__DIR__, 2) . '/views';
        $templateFile = $viewsPath . '/' . ltrim($template, '/') . '.php';

        if (!file_exists($templateFile)) {
            throw new \RuntimeException("View not found: {$template}");
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $templateFile;
        $content = ob_get_clean();

        if ($layout !== null) {
            $layoutFile = $viewsPath . '/' . ltrim($layout, '/') . '.php';
            if (!file_exists($layoutFile)) {
                throw new \RuntimeException("Layout not found: {$layout}");
            }
            require $layoutFile;
        } else {
            echo $content;
        }
    }

    public static function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function setFlash(string $type, string $message): void
    {
        Security::startSession();
        $_SESSION['_flash'] = ['type' => $type, 'message' => $message];
    }

    public static function getFlash(): ?array
    {
        Security::startSession();
        if (!empty($_SESSION['_flash'])) {
            $flash = $_SESSION['_flash'];
            unset($_SESSION['_flash']);
            return $flash;
        }
        return null;
    }

    public static function statusBadge(string $status): array
    {
        return match ($status) {
            'verified'    => ['label' => 'Verified', 'class' => 'badge-verified', 'icon' => '✓'],
            'unconfirmed' => ['label' => 'Unconfirmed', 'class' => 'badge-unconfirmed', 'icon' => '!'],
            'debunked'    => ['label' => 'Debunked', 'class' => 'badge-debunked', 'icon' => '✕'],
            default       => ['label' => 'Coming at Launch', 'class' => 'badge-coming', 'icon' => '◷'],
        };
    }

    /**
     * Format controller button sequences into crisp inline DualSense / Xbox SVGs + tactile keycaps.
     */
    public static function formatButtonSequence(string $sequence, string $platform = 'ps5'): string
    {
        $trimmed = trim($sequence);
        if ($trimmed === '') {
            return '<span class="code-placeholder">Not yet announced</span>';
        }

        // If it's a status message rather than button inputs, render cleanly
        if (preg_match('/(Coming at launch|UNCONFIRMED|NO CODE|Pending)/i', $trimmed)) {
            return '<span class="code-placeholder">' . self::e($trimmed) . '</span>';
        }

        // Split comma or arrow/plus separated tokens like "R1, R1, Circle, R2, Left, Right"
        $tokens = preg_split('/\s*,\s*|\s+->\s+|\s+\+\s+/', $trimmed);
        if (!$tokens || count($tokens) <= 1) {
            return '<code class="code-mono">' . self::e($trimmed) . '</code>';
        }

        $plat = strtolower($platform);
        $html = '<span class="btn-sequence" data-platform="' . self::e($plat) . '">';
        foreach ($tokens as $tok) {
            $tok = trim($tok);
            if ($tok === '') {
                continue;
            }
            $lower = strtolower($tok);
            $cls = 'btn-key';
            $svg = self::buttonIconSvg($lower, $plat);

            if (in_array($lower, ['triangle', 'y', '△'], true)) {
                $cls .= ' key-triangle';
            } elseif (in_array($lower, ['circle', 'o', 'b', '○'], true)) {
                $cls .= ' key-circle';
            } elseif (in_array($lower, ['cross', 'x', 'a', '✕'], true)) {
                $cls .= ' key-cross';
            } elseif (in_array($lower, ['square', '□'], true)) {
                $cls .= ' key-square';
            } elseif (in_array($lower, ['l1', 'l2', 'r1', 'r2', 'lb', 'lt', 'rb', 'rt'], true)) {
                $cls .= ' key-shoulder';
            } elseif (in_array($lower, ['left', 'right', 'up', 'down', '←', '→', '↑', '↓'], true)) {
                $cls .= ' key-dpad';
            }

            if ($svg !== '') {
                $html .= '<kbd class="' . $cls . ' has-svg" title="' . self::e($tok) . '" aria-label="' . self::e($tok) . '">' . $svg . '<span class="key-lbl">' . self::e($tok) . '</span></kbd>';
            } else {
                $html .= '<kbd class="' . $cls . '">' . self::e($tok) . '</kbd>';
            }
        }
        $html .= '</span>';
        return $html;
    }

    private static function buttonIconSvg(string $token, string $platform): string
    {
        // PlayStation DualSense face buttons
        if ($token === 'triangle' || $token === '△') {
            return '<svg class="btn-svg" viewBox="0 0 20 20" width="14" height="14" aria-hidden="true"><polygon points="10,3 17,16 3,16" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linejoin="round"/></svg>';
        }
        if ($token === 'circle' || $token === 'o' || $token === '○') {
            return '<svg class="btn-svg" viewBox="0 0 20 20" width="14" height="14" aria-hidden="true"><circle cx="10" cy="10" r="6.5" fill="none" stroke="currentColor" stroke-width="2.1"/></svg>';
        }
        if ($token === 'cross' || $token === '✕' || ($token === 'x' && $platform === 'ps5')) {
            return '<svg class="btn-svg" viewBox="0 0 20 20" width="14" height="14" aria-hidden="true"><path d="M5 5l10 10M15 5L5 15" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg>';
        }
        if ($token === 'square' || $token === '□') {
            return '<svg class="btn-svg" viewBox="0 0 20 20" width="14" height="14" aria-hidden="true"><rect x="4" y="4" width="12" height="12" rx="1.5" fill="none" stroke="currentColor" stroke-width="2.1"/></svg>';
        }

        // Xbox face buttons (circular badge style)
        if (in_array($token, ['y', 'b', 'a'], true) || ($token === 'x' && $platform !== 'ps5')) {
            $letter = strtoupper($token);
            return '<svg class="btn-svg btn-svg-xbox" viewBox="0 0 20 20" width="15" height="15" aria-hidden="true"><circle cx="10" cy="10" r="8.2" fill="currentColor" opacity="0.14"/><circle cx="10" cy="10" r="8.2" fill="none" stroke="currentColor" stroke-width="1.6"/><text x="10" y="13.8" text-anchor="middle" font-family="Consolas, monospace" font-weight="700" font-size="11" fill="currentColor">' . $letter . '</text></svg>';
        }

        // D-Pad Directional Arrows
        if ($token === 'left' || $token === '←') {
            return '<svg class="btn-svg" viewBox="0 0 20 20" width="13" height="13" aria-hidden="true"><path d="M13 4L6 10l7 6V4z" fill="currentColor"/></svg>';
        }
        if ($token === 'right' || $token === '→') {
            return '<svg class="btn-svg" viewBox="0 0 20 20" width="13" height="13" aria-hidden="true"><path d="M7 4l7 6-7 6V4z" fill="currentColor"/></svg>';
        }
        if ($token === 'up' || $token === '↑') {
            return '<svg class="btn-svg" viewBox="0 0 20 20" width="13" height="13" aria-hidden="true"><path d="M4 13l6-7 6 7H4z" fill="currentColor"/></svg>';
        }
        if ($token === 'down' || $token === '↓') {
            return '<svg class="btn-svg" viewBox="0 0 20 20" width="13" height="13" aria-hidden="true"><path d="M4 7l6 7 6-7H4z" fill="currentColor"/></svg>';
        }

        // Shoulder / Trigger silhouettes
        if (in_array($token, ['l1', 'l2', 'lb', 'lt'], true)) {
            return '<svg class="btn-svg" viewBox="0 0 22 16" width="15" height="11" aria-hidden="true"><path d="M3 13V8c0-3.3 2.7-5 7-5h9v10H3z" fill="none" stroke="currentColor" stroke-width="1.8"/></svg>';
        }
        if (in_array($token, ['r1', 'r2', 'rb', 'rt'], true)) {
            return '<svg class="btn-svg" viewBox="0 0 22 16" width="15" height="11" aria-hidden="true"><path d="M19 13V8c0-3.3-2.7-5-7-5H3v10h16z" fill="none" stroke="currentColor" stroke-width="1.8"/></svg>';
        }

        return '';
    }

    /**
     * Extract an 11-character YouTube video ID from any standard YouTube / Live / Shorts URL or raw ID.
     */
    public static function extractYoutubeId(string $input): ?string
    {
        $val = trim($input);
        if ($val === '') {
            return null;
        }
        if (preg_match('/^[A-Za-z0-9_-]{11}$/', $val)) {
            return $val;
        }
        if (preg_match('~(?:youtube\.com/(?:watch\?(?:.*&)?v=|embed/|live/|shorts/|v/)|youtu\.be/)([A-Za-z0-9_-]{11})~i', $val, $m)) {
            return $m[1];
        }
        return null;
    }
}
