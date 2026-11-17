<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Seo;
use App\Models\BlogPost;
use App\Models\Cheat;

final class SearchController extends Controller
{
    public function index(): void
    {
        $query = trim((string) ($_GET['q'] ?? ''));
        $cheats = [];
        $posts = [];

        if ($query !== '' && mb_strlen($query) >= 2) {
            $cheats = Cheat::searchFullText($query, 20);
            $posts = BlogPost::searchFullText($query, 12);
        }

        $breadcrumbs = [
            ['label' => 'Home', 'url' => '/'],
            ['label' => 'Search', 'url' => '/search'],
        ];

        $seo = Seo::buildMeta([
            'title'  => ($query !== '' ? 'Search: ' . $query : 'Search GTA 6 Cheats & Guides') . ' | ViceCodes',
            'robots' => 'noindex, follow',
            'schemas' => [Seo::breadcrumbSchema($breadcrumbs)],
        ], $this->settings);

        $this->render('public/search', [
            'seo'         => $seo,
            'query'       => $query,
            'cheats'      => $cheats,
            'posts'       => $posts,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }
}
