<?php
declare(strict_types=1);

namespace App\Core;

use App\Models\Cheat;

/**
 * Internal Linking Automation Helper
 * Safely adds contextual internal links to known GTA 6 cheats inside editorial prose.
 */
final class AutoLinker
{
    public static function enrich(string $html, string $currentSlug = '', int $maxLinks = 4): string
    {
        if (trim($html) === '' || $maxLinks <= 0) {
            return $html;
        }

        // Load cheats for keyword matching
        try {
            $cheats = Cheat::listFiltered([], 40);
        } catch (\Throwable) {
            return $html;
        }

        $targets = [];
        foreach ($cheats as $c) {
            $slug = (string) ($c['slug'] ?? '');
            $title = trim((string) ($c['title'] ?? ''));
            if ($slug === '' || $slug === $currentSlug || mb_strlen($title) < 5) {
                continue;
            }
            $targets[$title] = Url::to('/cheats/' . $slug);
            // Also match short root if formatted as "Title (Details)"
            if (preg_match('/^([^(]{5,40})\s*\(/', $title, $m)) {
                $short = trim($m[1]);
                if (!isset($targets[$short])) {
                    $targets[$short] = Url::to('/cheats/' . $slug);
                }
            }
        }

        if ($targets === []) {
            return $html;
        }

        // Sort longest phrases first to avoid partial match collisions
        uksort($targets, static fn(string $a, string $b) => mb_strlen($b) <=> mb_strlen($a));

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $prevErr = libxml_use_internal_errors(true);
        $wrapped = '<?xml encoding="utf-8" ?><div id="vc-autolink-root">' . $html . '</div>';
        $loaded = @$dom->loadHTML($wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($prevErr);

        if (!$loaded) {
            return $html;
        }

        $xpath = new \DOMXPath($dom);
        // Only link inside plain text nodes whose ancestors do NOT include a, code, kbd, pre, h1..h6
        $textNodes = $xpath->query('//text()[not(ancestor::a) and not(ancestor::code) and not(ancestor::kbd) and not(ancestor::pre) and not(ancestor::h1) and not(ancestor::h2) and not(ancestor::h3) and not(ancestor::h4)]');
        if (!$textNodes) {
            return $html;
        }

        $linkedUrls = [];
        $count = 0;

        foreach ($textNodes as $node) {
            if ($count >= $maxLinks) {
                break;
            }
            $text = $node->nodeValue ?? '';
            if (trim($text) === '') {
                continue;
            }

            foreach ($targets as $phrase => $url) {
                if ($count >= $maxLinks) {
                    break;
                }
                if (isset($linkedUrls[$url])) {
                    continue;
                }
                $pattern = '/\b(' . preg_quote($phrase, '/') . ')\b/iu';
                if (preg_match($pattern, $text, $match, PREG_OFFSET_CAPTURE)) {
                    $matchedStr = $match[1][0];
                    $offset = (int) $match[1][1];
                    $before = substr($text, 0, $offset);
                    $after  = substr($text, $offset + strlen($matchedStr));

                    $parent = $node->parentNode;
                    if (!$parent) {
                        continue;
                    }
                    if ($before !== '') {
                        $parent->insertBefore($dom->createTextNode($before), $node);
                    }
                    $a = $dom->createElement('a');
                    $a->setAttribute('href', $url);
                    $a->setAttribute('class', 'vc-autolink');
                    $a->appendChild($dom->createTextNode($matchedStr));
                    $parent->insertBefore($a, $node);

                    $oldNode = $node;
                    if ($after !== '') {
                        $afterNode = $dom->createTextNode($after);
                        $parent->insertBefore($afterNode, $oldNode);
                        $parent->removeChild($oldNode);
                        $node = $afterNode;
                        $text = $after;
                    } else {
                        $parent->removeChild($oldNode);
                        $linkedUrls[$url] = true;
                        $count++;
                        break;
                    }

                    $linkedUrls[$url] = true;
                    $count++;
                }
            }
        }

        $root = $dom->getElementById('vc-autolink-root');
        if (!$root) {
            return $html;
        }
        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $dom->saveHTML($child);
        }
        return $out;
    }
}
