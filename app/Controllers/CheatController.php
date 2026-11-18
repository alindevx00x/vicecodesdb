<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\AutoLinker;
use App\Core\Controller;
use App\Core\OgImageGenerator;
use App\Core\PageCache;
use App\Core\Security;
use App\Core\Seo;
use App\Core\View;
use App\Models\Category;
use App\Models\Cheat;
use App\Models\Tag;

final class CheatController extends Controller
{
    public function index(): void
    {
        $filters = [
            'platform'      => $_GET['platform'] ?? '',
            'category_slug' => $_GET['category'] ?? '',
            'status'        => $_GET['status'] ?? '',
            'sort'          => $_GET['sort'] ?? 'order',
            'q'             => trim((string) ($_GET['q'] ?? '')),
        ];

        $cheats = Cheat::listFiltered($filters, 100);
        $statusCounts = Cheat::statusCounts();

        $sharedLoadout = [];
        $rawLoadout = trim((string) ($_GET['loadout'] ?? ''));
        if ($rawLoadout !== '') {
            $sharedLoadout = Cheat::findBySlugs(explode(',', $rawLoadout));
        }

        $breadcrumbs = [
            ['label' => 'Home', 'url' => '/'],
            ['label' => 'All GTA 6 Cheats', 'url' => '/cheats'],
        ];

        $listItems = array_map(static fn($c) => [
            'name' => $c['title'],
            'url'  => '/cheats/' . $c['slug'],
        ], array_slice($cheats, 0, 20));

        $seo = Seo::buildMeta([
            'title'       => 'All GTA 6 Cheat Codes (PS5, Xbox Series X|S, PC) - Live Tracker | ViceCodes',
            'description' => 'Filter every GTA 6 cheat code by platform, category, and verification status. One-click copy for PS5, Xbox Series X|S, and PC.',
            'schemas'     => [
                Seo::breadcrumbSchema($breadcrumbs),
                Seo::itemListSchema('GTA 6 Cheat Codes Directory', $listItems),
            ],
        ], $this->settings);

        $this->render('public/cheats/index', [
            'seo'          => $seo,
            'cheats'       => $cheats,
            'sharedLoadout'=> $sharedLoadout,
            'filters'      => $filters,
            'statusCounts' => $statusCounts,
            'breadcrumbs'  => $breadcrumbs,
            'pageHeading'  => 'All GTA 6 Cheat Codes',
            'pageIntro'    => 'Filter by PlayStation 5, Xbox Series X|S, PC, category, or verification badge. Zero fake confirmed codes.',
            'activePlatform' => $filters['platform'],
            'activeCategory' => null,
        ]);
    }

    public function printable(): void
    {
        $cheats = Cheat::listFiltered([], 250);
        $breadcrumbs = [
            ['label' => 'Home', 'url' => '/'],
            ['label' => 'Cheats', 'url' => '/cheats'],
            ['label' => 'Printable Cheat Sheet', 'url' => '/cheats/printable'],
        ];

        $seo = Seo::buildMeta([
            'title'       => 'GTA 6 All Cheat Codes Printable Table (PS5, Xbox, Phone) | ViceCodes',
            'description' => 'Single-page scannable & print-friendly GTA 6 cheat codes comparison table listing Effect, PS5 Sequence, Xbox Sequence, Phone Number, Patch, and Status.',
            'schemas'     => [
                Seo::breadcrumbSchema($breadcrumbs),
                Seo::itemListSchema('GTA 6 Printable Cheat Sheet', array_map(static fn($c) => ['name' => $c['title'], 'url' => '/cheats/' . $c['slug']], $cheats)),
            ],
        ], $this->settings);

        $this->render('public/cheats/printable', [
            'seo'         => $seo,
            'cheats'      => $cheats,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    public function filterAjax(): void
    {
        $filters = [
            'platform'      => $_GET['platform'] ?? '',
            'category_slug' => $_GET['category'] ?? '',
            'status'        => $_GET['status'] ?? '',
            'sort'          => $_GET['sort'] ?? 'order',
            'q'             => trim((string) ($_GET['q'] ?? '')),
        ];

        $cheats = Cheat::listFiltered($filters, 100);
        $activePlatform = $filters['platform'];

        ob_start();
        if ($cheats === []) {
            echo '<div class="empty-state glass-card"><h3>No cheats match these filters</h3><p>Try clearing your platform or status filter to view all tracked GTA 6 cheat codes.</p></div>';
        } else {
            foreach ($cheats as $cheat) {
                require dirname(__DIR__, 2) . '/views/partials/cheat-card.php';
            }
        }
        $html = ob_get_clean();

        $this->json([
            'count' => count($cheats),
            'html'  => $html,
        ]);
    }

    public function apiJson(): void
    {
        $cacheFile = PageCache::cacheDir() . '/api_cheats.json';
        if (PageCache::isEnabled() && file_exists($cacheFile) && (time() - (int) @filemtime($cacheFile)) < 600) {
            $cached = @file_get_contents($cacheFile);
            if ($cached !== false && $cached !== '') {
                header('Content-Type: application/json; charset=utf-8');
                header('Cache-Control: public, max-age=300');
                header('X-VC-Cache: HIT');
                echo $cached;
                return;
            }
        }

        $cheats = Cheat::listFiltered([], 500);
        $items = [];
        foreach ($cheats as $c) {
            $plats = $c['platforms'] ?? [];
            $phone = $plats['ps5']['cell_number']
                ?? ($plats['xbox']['cell_number'] ?? ($plats['pc']['cell_number'] ?? ''));
            $items[] = [
                'id'            => (int) $c['id'],
                'title'         => (string) $c['title'],
                'slug'          => (string) $c['slug'],
                'url'           => \App\Core\Url::to('/cheats/' . $c['slug']),
                'category'      => (string) ($c['category_name'] ?? 'Uncategorized'),
                'category_slug' => (string) ($c['category_slug'] ?? ''),
                'status'        => (string) $c['status'],
                'patch'         => (string) ($c['game_patch'] ?? 'v1.00'),
                'short'         => (string) $c['short_description'],
                'ps5'           => (string) ($plats['ps5']['code_sequence'] ?? ''),
                'xbox'          => (string) ($plats['xbox']['code_sequence'] ?? ''),
                'pc'            => (string) ($plats['pc']['code_sequence'] ?? ''),
                'phone'         => (string) $phone,
                'preview_image' => (string) ($c['preview_image'] ?? ''),
            ];
        }

        $payload = json_encode([
            'generated_at' => gmdate('c'),
            'count'        => count($items),
            'cheats'       => $items,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $dir = PageCache::cacheDir();
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        if ($payload !== false) {
            @file_put_contents($cacheFile, $payload, LOCK_EX);
        }

        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: public, max-age=300');
        header('X-VC-Cache: MISS');
        echo $payload ?: '{"cheats":[]}';
    }

    public function category(array $params): void
    {
        $shortMap = [
            'weapons'   => 'weapons-combat',
            'health'    => 'health-armor',
            'vehicles'  => 'vehicles-spawns',
            'wanted'    => 'wanted-level',
            'abilities' => 'player-abilities',
            'weather'   => 'weather-world',
        ];
        $rawSlug = strtolower((string) ($params['slug'] ?? basename(\App\Core\Url::currentPath())));
        $slug = $shortMap[$rawSlug] ?? $rawSlug;
        $category = Category::findBySlug($slug, 'cheat');
        if (!$category) {
            (new PageController())->notFound();
            return;
        }

        $cheats = Cheat::listFiltered(['category_slug' => $slug], 100);
        $statusCounts = Cheat::statusCounts();

        $breadcrumbs = [
            ['label' => 'Home', 'url' => '/'],
            ['label' => 'Cheats', 'url' => '/cheats'],
            ['label' => $category['name'], 'url' => '/cheats/category/' . $category['slug']],
        ];

        $listItems = array_map(static fn($c) => [
            'name' => $c['title'],
            'url'  => '/cheats/' . $c['slug'],
        ], $cheats);

        $seo = Seo::buildMeta([
            'title'       => $category['meta_title'] ?: ($category['name'] . ' - GTA 6 Cheat Codes | ViceCodes'),
            'description' => $category['meta_description'] ?: $category['description'],
            'canonical'   => \App\Core\Url::full('/cheats/category/' . $category['slug']),
            'schemas'     => [
                Seo::breadcrumbSchema($breadcrumbs),
                Seo::itemListSchema($category['name'] . ' GTA 6 Cheats', $listItems),
            ],
        ], $this->settings);

        $this->render('public/cheats/index', [
            'seo'            => $seo,
            'cheats'         => $cheats,
            'filters'        => ['category_slug' => $slug, 'platform' => '', 'status' => '', 'sort' => 'order', 'q' => ''],
            'statusCounts'   => $statusCounts,
            'breadcrumbs'    => $breadcrumbs,
            'pageHeading'    => 'GTA 6 ' . $category['name'] . ' Cheats',
            'pageIntro'      => $category['description'] ?? '',
            'activePlatform' => '',
            'activeCategory' => $category,
        ]);
    }

    public function platform(array $params): void
    {
        $plat = strtolower((string) ($params['platform'] ?? basename(\App\Core\Url::currentPath())));
        $metaMap = [
            'ps5' => [
                'name'  => 'PS5 (PlayStation 5)',
                'title' => 'GTA 6 Cheat Codes for PS5 (DualSense Button List) | ViceCodes',
                'desc'  => 'Complete GTA 6 PS5 cheat codes list with DualSense button combinations, phone dial numbers, and live verification status.',
                'intro' => 'Every GTA 6 cheat code tracked for PlayStation 5 DualSense controllers and in-game smartphone dialing.',
            ],
            'xbox' => [
                'name'  => 'Xbox Series X|S',
                'title' => 'GTA 6 Cheat Codes for Xbox Series X|S | ViceCodes',
                'desc'  => 'Complete GTA 6 Xbox Series X and Series S cheat codes with controller sequences, phone numbers, and verification badges.',
                'intro' => 'Every GTA 6 cheat code tracked for Xbox Series X|S controllers and smartphone dialing.',
            ],
            'pc' => [
                'name'  => 'PC',
                'title' => 'GTA 6 Cheat Codes for PC (Console Commands & Phone) | ViceCodes',
                'desc'  => 'Track GTA 6 PC cheat codes, tilde console commands, and phone dial codes ahead of the PC release announcement.',
                'intro' => 'PC release details and future tilde console / phone cheat codes for Grand Theft Auto VI on PC.',
            ],
        ];

        if (!isset($metaMap[$plat])) {
            (new PageController())->notFound();
            return;
        }

        $info = $metaMap[$plat];
        $cheats = Cheat::listFiltered(['platform' => $plat], 100);
        $statusCounts = Cheat::statusCounts();

        $breadcrumbs = [
            ['label' => 'Home', 'url' => '/'],
            ['label' => 'Cheats', 'url' => '/cheats'],
            ['label' => $info['name'], 'url' => '/cheats/' . $plat],
        ];

        $seo = Seo::buildMeta([
            'title'       => $info['title'],
            'description' => $info['desc'],
            'schemas'     => [
                Seo::breadcrumbSchema($breadcrumbs),
                Seo::itemListSchema('GTA 6 ' . $info['name'] . ' Cheats', array_map(static fn($c) => ['name' => $c['title'], 'url' => '/cheats/' . $c['slug']], $cheats)),
            ],
        ], $this->settings);

        $this->render('public/cheats/index', [
            'seo'            => $seo,
            'cheats'         => $cheats,
            'filters'        => ['platform' => $plat, 'category_slug' => '', 'status' => '', 'sort' => 'order', 'q' => ''],
            'statusCounts'   => $statusCounts,
            'breadcrumbs'    => $breadcrumbs,
            'pageHeading'    => 'GTA 6 Cheat Codes: ' . $info['name'],
            'pageIntro'      => $info['intro'],
            'activePlatform' => $plat,
            'activeCategory' => null,
        ]);
    }

    public function show(array $params): void
    {
        $slug = (string) ($params['slug'] ?? '');
        $cheat = Cheat::findBySlug($slug);
        if (!$cheat) {
            (new PageController())->notFound();
            return;
        }

        Cheat::incrementViews((int) $cheat['id']);
        $relatedCheats = Cheat::related((int) $cheat['id'], $cheat['category_id'] ? (int) $cheat['category_id'] : null, 4);
        $tags = Tag::forCheat((int) $cheat['id']);
        $relatedGuides = Tag::relatedPostsForCheat((int) $cheat['id'], 3);

        if (!empty($cheat['content'])) {
            $cheat['content'] = AutoLinker::enrich((string) $cheat['content'], $slug, 4);
        }

        $faqs = json_decode((string) ($cheat['faqs_json'] ?? '[]'), true) ?: [];
        $changelog = json_decode((string) ($cheat['changelog_json'] ?? '[]'), true) ?: [];

        $breadcrumbs = [
            ['label' => 'Home', 'url' => '/'],
            ['label' => 'Cheats', 'url' => '/cheats'],
        ];
        if (!empty($cheat['category_name'])) {
            $breadcrumbs[] = ['label' => $cheat['category_name'], 'url' => '/cheats/category/' . $cheat['category_slug']];
        }
        $breadcrumbs[] = ['label' => $cheat['title'], 'url' => '/cheats/' . $cheat['slug']];

        $schemas = [
            Seo::breadcrumbSchema($breadcrumbs),
            Seo::howToSchema(
                'How to Activate ' . $cheat['title'] . ' in GTA 6',
                $cheat['short_description'],
                [
                    ['name' => 'Load GTA 6 Story Mode', 'text' => 'Enter single-player free roam in Leonida (cheats do not work in GTA Online) and create a manual backup save first.'],
                    ['name' => 'Enter the Platform Input', 'text' => 'Input the PS5 DualSense or Xbox Series X|S button sequence quickly without opening the pause menu, or dial the phone cheat if available.'],
                    ['name' => 'Watch for Confirmation', 'text' => 'Look for the small notification banner above the mini-map confirming the cheat effect is active.'],
                ]
            ),
        ];
        if ($faqSchema = Seo::faqSchema($faqs)) {
            $schemas[] = $faqSchema;
        }

        $ogImage = !empty($cheat['og_image'])
            ? $cheat['og_image']
            : ('/og/cheat/' . $cheat['slug'] . '.png');

        $seo = Seo::buildMeta([
            'title'       => $cheat['meta_title'] ?: ($cheat['title'] . ' - GTA 6 Cheat Code | ViceCodes'),
            'description' => $cheat['meta_description'] ?: $cheat['short_description'],
            'keywords'    => $cheat['focus_keyword'] ?: ($cheat['title'] . ', GTA 6 cheat codes, PS5, Xbox'),
            'og_image'    => $ogImage,
            'schemas'     => $schemas,
        ], $this->settings);

        $this->render('public/cheats/show', [
            'seo'           => $seo,
            'cheat'         => $cheat,
            'tags'          => $tags,
            'relatedGuides' => $relatedGuides,
            'relatedCheats' => $relatedCheats,
            'faqs'          => $faqs,
            'changelog'     => $changelog,
            'breadcrumbs'   => $breadcrumbs,
        ]);
    }

    public function feedback(array $params): void
    {
        if (!Security::verifyCsrf()) {
            $this->json(['ok' => false, 'message' => 'Security token expired. Refresh the page and retry.'], 403);
        }

        $id = (int) ($params['id'] ?? 0);
        if ($id <= 0) {
            $this->json(['ok' => false, 'message' => 'Invalid cheat ID.'], 400);
        }

        if (!Security::checkRateLimit('cheat_vote_' . $id, 5, 600)) {
            $this->json(['ok' => false, 'message' => 'You have already submitted feedback recently.'], 429);
        }

        $vote = strtolower(trim((string) ($_POST['vote'] ?? '')));
        if (!in_array($vote, ['working', 'broken'], true)) {
            $this->json(['ok' => false, 'message' => 'Invalid vote choice.'], 422);
        }

        $updated = Cheat::recordFeedback($id, $vote === 'working');
        if (!$updated) {
            $this->json(['ok' => false, 'message' => 'Cheat not found.'], 404);
        }

        PageCache::invalidateAll();

        $working = (int) $updated['votes_working'];
        $broken = (int) $updated['votes_broken'];
        $total = $working + $broken;
        $pct = $total > 0 ? (int) round(($working / $total) * 100) : 100;

        $this->json([
            'ok'            => true,
            'votes_working' => $working,
            'votes_broken'  => $broken,
            'total'         => $total,
            'working_pct'   => $pct,
            'needs_retest'  => !empty($updated['needs_retest']),
            'message'       => $vote === 'working'
                ? 'Thanks! Confirmed working on current patch.'
                : 'Thanks for reporting! Our verification team has been notified.',
        ]);
    }

    public function ogImage(array $params): void
    {
        $slug = (string) ($params['slug'] ?? '');
        $cheat = Cheat::findBySlug($slug);
        if (!$cheat) {
            http_response_code(404);
            exit;
        }

        $path = OgImageGenerator::generateForCheat($cheat);
        if ($path === null || !file_exists($path)) {
            $fallback = dirname(__DIR__, 2) . '/public/assets/img/og-default.png';
            if (file_exists($fallback)) {
                header('Content-Type: image/png');
                readfile($fallback);
                exit;
            }
            http_response_code(404);
            exit;
        }

        header('Content-Type: image/png');
        header('Cache-Control: public, max-age=86400');
        readfile($path);
        exit;
    }
}
