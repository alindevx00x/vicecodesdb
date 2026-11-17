<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Url;
use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Cheat;
use App\Models\Page;

final class SeoController extends Controller
{
    public function sitemapIndex(): void
    {
        header('Content-Type: application/xml; charset=utf-8');
        $now = date('c');
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach (['/sitemap-pages.xml', '/sitemap-cheats.xml', '/sitemap-blog.xml'] as $sm) {
            echo "  <sitemap>\n";
            echo '    <loc>' . htmlspecialchars(Url::full($sm), ENT_XML1) . "</loc>\n";
            echo "    <lastmod>{$now}</lastmod>\n";
            echo "  </sitemap>\n";
        }
        echo '</sitemapindex>';
        exit;
    }

    public function sitemapPages(): void
    {
        header('Content-Type: application/xml; charset=utf-8');
        $urls = [
            ['loc' => Url::full('/'), 'priority' => '1.0', 'freq' => 'daily'],
            ['loc' => Url::full('/cheats'), 'priority' => '0.95', 'freq' => 'daily'],
            ['loc' => Url::full('/cheats/printable'), 'priority' => '0.9', 'freq' => 'daily'],
            ['loc' => Url::full('/cheats/ps5'), 'priority' => '0.9', 'freq' => 'daily'],
            ['loc' => Url::full('/cheats/xbox'), 'priority' => '0.9', 'freq' => 'daily'],
            ['loc' => Url::full('/cheats/pc'), 'priority' => '0.8', 'freq' => 'weekly'],
            ['loc' => Url::full('/blog'), 'priority' => '0.85', 'freq' => 'daily'],
        ];

        foreach (Category::byType('cheat') as $cat) {
            $urls[] = ['loc' => Url::full('/cheats/category/' . $cat['slug']), 'priority' => '0.85', 'freq' => 'daily'];
        }
        foreach (\App\Models\Tag::allWithCounts() as $tag) {
            $urls[] = ['loc' => Url::full('/tag/' . $tag['slug']), 'priority' => '0.75', 'freq' => 'weekly'];
        }
        foreach (Page::all() as $page) {
            if ($page['status'] === 'published') {
                $urls[] = ['loc' => Url::full('/' . $page['slug']), 'priority' => '0.6', 'freq' => 'monthly'];
            }
        }

        $this->renderUrlSet($urls);
    }

    public function indexNowKey(array $params = []): void
    {
        $configured = trim((string) ($this->settings['indexnow_key'] ?? ''));
        $requested = (string) ($params['key'] ?? '');
        if ($configured === '' || !hash_equals($configured, $requested)) {
            (new PageController())->notFound();
            return;
        }
        header('Content-Type: text/plain; charset=utf-8');
        echo $configured;
        exit;
    }

    public function sitemapCheats(): void
    {
        header('Content-Type: application/xml; charset=utf-8');
        $urls = [];
        foreach (Cheat::allSlugsForSitemap() as $row) {
            $urls[] = [
                'loc'      => Url::full('/cheats/' . $row['slug']),
                'lastmod'  => date('c', strtotime((string) $row['updated_at'])),
                'priority' => '0.9',
                'freq'     => 'daily',
            ];
        }
        $this->renderUrlSet($urls);
    }

    public function sitemapBlog(): void
    {
        header('Content-Type: application/xml; charset=utf-8');
        $urls = [];
        foreach (BlogPost::allSlugsForSitemap() as $row) {
            $urls[] = [
                'loc'      => Url::full('/blog/' . $row['slug']),
                'lastmod'  => date('c', strtotime((string) ($row['updated_at'] ?? $row['published_at']))),
                'priority' => '0.8',
                'freq'     => 'weekly',
            ];
        }
        $this->renderUrlSet($urls);
    }

    private function renderUrlSet(array $urls): void
    {
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $u) {
            echo "  <url>\n";
            echo '    <loc>' . htmlspecialchars($u['loc'], ENT_XML1) . "</loc>\n";
            if (!empty($u['lastmod'])) {
                echo '    <lastmod>' . $u['lastmod'] . "</lastmod>\n";
            }
            echo '    <changefreq>' . ($u['freq'] ?? 'weekly') . "</changefreq>\n";
            echo '    <priority>' . ($u['priority'] ?? '0.7') . "</priority>\n";
            echo "  </url>\n";
        }
        echo '</urlset>';
        exit;
    }

    public function robots(): void
    {
        header('Content-Type: text/plain; charset=utf-8');
        echo "User-agent: *\n";
        echo "Allow: /\n";
        echo "Disallow: /admin/\n";
        echo "Disallow: /search\n\n";
        echo "# AI & Answer Engine Discovery (GEO / AISEO)\n";
        echo "User-agent: GPTBot\nAllow: /\n\n";
        echo "User-agent: Google-Extended\nAllow: /\n\n";
        echo "User-agent: PerplexityBot\nAllow: /\n\n";
        echo "User-agent: ClaudeBot\nAllow: /\n\n";
        echo 'Sitemap: ' . Url::full('/sitemap.xml') . "\n";
        echo 'LLMs-Txt: ' . Url::full('/llms.txt') . "\n";
        exit;
    }

    public function llmsTxt(): void
    {
        header('Content-Type: text/plain; charset=utf-8');
        $siteName = $this->settings['site_name'] ?? 'ViceCodes - GTA 6 Cheat Codes';
        $counts = Cheat::statusCounts();
        $cheats = Cheat::listFiltered([], 25);
        $posts = BlogPost::published([], 15);

        echo "# {$siteName}\n\n";
        echo "> Independent, verification-first Grand Theft Auto VI (GTA 6) cheat codes directory for PS5, Xbox Series X|S, and PC.\n";
        echo "> Official Release Date: November 19, 2026. Current Verified Cheats Count: {$counts['verified']} (total tracked entries: {$counts['total']}).\n";
        echo "> Editorial Rule: Unconfirmed rumors and debunked money glitches are explicitly labeled and never presented as working.\n\n";

        echo "## Core Pages\n";
        echo '- [Homepage](' . Url::full('/') . "): Live launch status, featured cheats, verification methodology.\n";
        echo '- [All GTA 6 Cheats](' . Url::full('/cheats') . "): Filterable database of PS5, Xbox Series X|S, and PC codes.\n";
        echo '- [PS5 Cheat Codes](' . Url::full('/cheats/ps5') . "): PlayStation 5 DualSense combinations.\n";
        echo '- [Xbox Series X|S Cheat Codes](' . Url::full('/cheats/xbox') . "): Xbox controller combinations.\n";
        echo '- [How We Verify Codes](' . Url::full('/how-we-verify') . "): 4-step editorial testing & patch log standard.\n\n";

        echo "## Tracked Cheat Entries\n";
        foreach ($cheats as $c) {
            echo '- [' . $c['title'] . '](' . Url::full('/cheats/' . $c['slug']) . ') [' . strtoupper($c['status']) . ']: ' . $c['short_description'] . "\n";
        }

        echo "\n## Guides & Articles\n";
        foreach ($posts as $p) {
            echo '- [' . $p['title'] . '](' . Url::full('/blog/' . $p['slug']) . '): ' . ($p['quick_answer'] ?: $p['excerpt']) . "\n";
        }
        exit;
    }

    public function rssFeed(): void
    {
        header('Content-Type: application/rss+xml; charset=utf-8');
        $posts = BlogPost::published([], 20);
        $siteTitle = $this->settings['site_name'] ?? 'ViceCodes - GTA 6 Cheat Codes';
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<rss version="2.0"><channel>';
        echo '<title>' . htmlspecialchars($siteTitle, ENT_XML1) . '</title>';
        echo '<link>' . htmlspecialchars(Url::full('/'), ENT_XML1) . '</link>';
        echo '<description>Verified GTA 6 Cheat Codes, Launch Updates &amp; Leonida Guides</description>';
        foreach ($posts as $p) {
            echo '<item>';
            echo '<title>' . htmlspecialchars($p['title'], ENT_XML1) . '</title>';
            echo '<link>' . htmlspecialchars(Url::full('/blog/' . $p['slug']), ENT_XML1) . '</link>';
            echo '<guid>' . htmlspecialchars(Url::full('/blog/' . $p['slug']), ENT_XML1) . '</guid>';
            echo '<pubDate>' . date(DATE_RSS, strtotime((string) ($p['published_at'] ?? $p['created_at']))) . '</pubDate>';
            echo '<description>' . htmlspecialchars((string) ($p['excerpt'] ?? $p['quick_answer'] ?? ''), ENT_XML1) . '</description>';
            echo '</item>';
        }
        echo '</channel></rss>';
        exit;
    }
}
