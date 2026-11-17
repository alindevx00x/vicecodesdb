<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\AutoLinker;
use App\Core\Controller;
use App\Core\Seo;
use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;

final class BlogController extends Controller
{
    public function index(): void
    {
        $catSlug = $_GET['category'] ?? '';
        $posts = BlogPost::published(['category_slug' => $catSlug], 18);
        $blogCategories = Category::byType('blog');

        $breadcrumbs = [
            ['label' => 'Home', 'url' => '/'],
            ['label' => 'Blog & Guides', 'url' => '/blog'],
        ];

        $seo = Seo::buildMeta([
            'title'       => 'GTA 6 Cheat Guides, News & Launch Updates | ViceCodes Blog',
            'description' => 'In-depth guides on entering GTA 6 cheat codes, trophy warnings, verification updates, and Leonida secrets.',
            'schemas'     => [Seo::breadcrumbSchema($breadcrumbs)],
        ], $this->settings);

        $this->render('public/blog/index', [
            'seo'            => $seo,
            'posts'          => $posts,
            'blogCategories' => $blogCategories,
            'activeCat'      => $catSlug,
            'breadcrumbs'    => $breadcrumbs,
        ]);
    }

    public function category(array $params): void
    {
        $slug = (string) ($params['slug'] ?? '');
        $cat = Category::findBySlug($slug, 'blog');
        if (!$cat) {
            (new PageController())->notFound();
            return;
        }

        $posts = BlogPost::published(['category_slug' => $slug], 18);
        $blogCategories = Category::byType('blog');

        $breadcrumbs = [
            ['label' => 'Home', 'url' => '/'],
            ['label' => 'Blog', 'url' => '/blog'],
            ['label' => $cat['name'], 'url' => '/blog/category/' . $cat['slug']],
        ];

        $seo = Seo::buildMeta([
            'title'       => $cat['meta_title'] ?: ($cat['name'] . ' - GTA 6 Guides | ViceCodes'),
            'description' => $cat['meta_description'] ?: $cat['description'],
            'schemas'     => [Seo::breadcrumbSchema($breadcrumbs)],
        ], $this->settings);

        $this->render('public/blog/index', [
            'seo'            => $seo,
            'posts'          => $posts,
            'blogCategories' => $blogCategories,
            'activeCat'      => $slug,
            'activeCategory' => $cat,
            'breadcrumbs'    => $breadcrumbs,
        ]);
    }

    public function show(array $params): void
    {
        $slug = (string) ($params['slug'] ?? '');
        $isPreview = isset($_GET['preview']) && User::isLoggedIn();
        $post = BlogPost::findBySlug($slug, $isPreview);
        if (!$post) {
            (new PageController())->notFound();
            return;
        }

        if ($post['status'] === 'published') {
            BlogPost::incrementViews((int) $post['id']);
        }
        $related = BlogPost::related((int) $post['id'], $post['category_id'] ? (int) $post['category_id'] : null, 3);
        $tags = Tag::forBlogPost((int) $post['id']);
        $post['content'] = AutoLinker::enrich((string) $post['content'], '', 4);
        $faqs = json_decode((string) ($post['faqs_json'] ?? '[]'), true) ?: [];

        $breadcrumbs = [
            ['label' => 'Home', 'url' => '/'],
            ['label' => 'Blog', 'url' => '/blog'],
            ['label' => $post['title'], 'url' => '/blog/' . $post['slug']],
        ];

        $schemas = [
            Seo::breadcrumbSchema($breadcrumbs),
            Seo::articleSchema($post, $post['author_name'] ?? 'ViceCodes Editorial Team', $this->settings),
        ];
        if ($faqSchema = Seo::faqSchema($faqs)) {
            $schemas[] = $faqSchema;
        }

        $seo = Seo::buildMeta([
            'title'       => $post['meta_title'] ?: ($post['title'] . ' | ViceCodes'),
            'description' => $post['meta_description'] ?: ($post['excerpt'] ?? $post['quick_answer'] ?? ''),
            'keywords'    => $post['focus_keyword'] ?? 'GTA 6 cheat codes, GTA VI guide',
            'robots'      => $isPreview ? 'noindex, nofollow' : 'index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1',
            'og_type'     => 'article',
            'og_image'    => $post['featured_image'] ?? null,
            'published'   => $post['published_at'] ?? $post['created_at'],
            'modified'    => $post['updated_at'],
            'schemas'     => $schemas,
        ], $this->settings);

        $this->render('public/blog/show', [
            'seo'         => $seo,
            'post'        => $post,
            'tags'        => $tags,
            'related'     => $related,
            'faqs'        => $faqs,
            'isPreview'   => $isPreview,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }
}
