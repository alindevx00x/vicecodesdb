<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Seo;
use App\Models\Tag;

final class TagController extends Controller
{
    public function show(array $params): void
    {
        $slug = (string) ($params['slug'] ?? '');
        $tag = Tag::findBySlug($slug);
        if (!$tag) {
            (new PageController())->notFound();
            return;
        }

        $cheats = Tag::cheatsByTag((int) $tag['id'], 60);
        $posts = Tag::postsByTag((int) $tag['id'], 24);
        $allTags = Tag::allWithCounts();

        $breadcrumbs = [
            ['label' => 'Home', 'url' => '/'],
            ['label' => 'Cheats', 'url' => '/cheats'],
            ['label' => '#' . $tag['name'], 'url' => '/tag/' . $tag['slug']],
        ];

        $seo = Seo::buildMeta([
            'title'       => $tag['name'] . ' - GTA 6 Cheats & Guides | ViceCodes',
            'description' => 'All GTA 6 cheat codes, platform sequences, and editorial guides tagged with ' . $tag['name'] . '.',
            'schemas'     => [
                Seo::breadcrumbSchema($breadcrumbs),
                Seo::itemListSchema($tag['name'] . ' GTA 6 Cheats', array_map(static fn($c) => ['name' => $c['title'], 'url' => '/cheats/' . $c['slug']], $cheats)),
            ],
        ], $this->settings);

        $this->render('public/tags/show', [
            'seo'         => $seo,
            'tag'         => $tag,
            'cheats'      => $cheats,
            'posts'       => $posts,
            'allTags'     => $allTags,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }
}
