<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\IndexNow;
use App\Core\PageCache;
use App\Core\Security;
use App\Core\Url;
use App\Core\View;
use App\Models\ActivityLog;
use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Tag;

final class BlogAdminController extends Controller
{
    public function index(): void
    {
        $user = $this->requireAdmin();
        $posts = BlogPost::allAdmin();
        $this->render('admin/blog/index', [
            'pageTitle' => 'Manage Blog Posts',
            'adminUser' => $user,
            'posts'     => $posts,
        ], 'layouts/admin');
    }

    public function create(): void
    {
        $user = $this->requireAdmin();
        $categories = Category::byType('blog');
        $this->render('admin/blog/form', [
            'pageTitle'  => 'New Blog Post',
            'adminUser'  => $user,
            'post'       => null,
            'categories' => $categories,
            'tagsCsv'    => '',
        ], 'layouts/admin');
    }

    public function edit(array $params): void
    {
        $user = $this->requireAdmin();
        $post = BlogPost::find((int) ($params['id'] ?? 0));
        if (!$post) {
            View::setFlash('error', 'Post not found.');
            $this->redirect('/admin/blog');
        }
        $categories = Category::byType('blog');
        $tags = Tag::forBlogPost((int) $post['id']);
        $tagsCsv = implode(', ', array_map(static fn($t) => $t['name'], $tags));
        $this->render('admin/blog/form', [
            'pageTitle'  => 'Edit Post: ' . $post['title'],
            'adminUser'  => $user,
            'post'       => $post,
            'categories' => $categories,
            'tagsCsv'    => $tagsCsv,
        ], 'layouts/admin');
    }

    public function save(array $params = []): void
    {
        $user = $this->requireAdmin();
        $this->requirePostCsrf();

        $id = !empty($params['id']) ? (int) $params['id'] : null;
        $existing = $id ? BlogPost::find($id) : null;
        $oldStatus = $existing['status'] ?? null;

        $title = trim((string) ($_POST['title'] ?? ''));
        $slug = trim((string) ($_POST['slug'] ?? '')) ?: Url::slugify($title);

        if ($title === '') {
            View::setFlash('error', 'Post title is required.');
            $this->redirect($id ? "/admin/blog/{$id}/edit" : '/admin/blog/create');
        }

        $faqs = [];
        $faqQs = $_POST['faq_q'] ?? [];
        $faqAs = $_POST['faq_a'] ?? [];
        for ($i = 0, $n = count($faqQs); $i < $n; $i++) {
            $q = trim((string) ($faqQs[$i] ?? ''));
            $a = trim((string) ($faqAs[$i] ?? ''));
            if ($q !== '' && $a !== '') {
                $faqs[] = ['q' => $q, 'a' => $a];
            }
        }

        $pubAt = trim((string) ($_POST['published_at'] ?? ''));
        $pubAt = $pubAt !== '' ? str_replace('T', ' ', $pubAt) : date('Y-m-d H:i:s');
        $newStatus = (string) ($_POST['status'] ?? 'draft');
        $cleanSlug = Url::slugify($slug);

        $data = [
            'title'              => $title,
            'slug'               => $cleanSlug,
            'excerpt'            => trim((string) ($_POST['excerpt'] ?? '')),
            'quick_answer'       => trim((string) ($_POST['quick_answer'] ?? '')),
            'content'            => Security::sanitizeRichHtml($_POST['content'] ?? ''),
            'category_id'        => $_POST['category_id'] ?? null,
            'featured_image'     => trim((string) ($_POST['featured_image'] ?? '')),
            'featured_image_alt' => trim((string) ($_POST['featured_image_alt'] ?? '')),
            'status'             => $newStatus,
            'author_id'          => (int) $user['id'],
            'meta_title'         => trim((string) ($_POST['meta_title'] ?? '')),
            'meta_description'   => trim((string) ($_POST['meta_description'] ?? '')),
            'focus_keyword'      => trim((string) ($_POST['focus_keyword'] ?? '')),
            'faqs_json'          => json_encode($faqs, JSON_UNESCAPED_UNICODE),
            'published_at'       => $pubAt,
        ];

        $savedId = BlogPost::save($data, $id);
        Tag::syncForBlogPost($savedId, (string) ($_POST['tags'] ?? ''));
        PageCache::invalidateAll();

        $pingMsg = '';
        if ($newStatus === 'published' && $oldStatus !== 'published') {
            IndexNow::pingUrls(['/blog/' . $cleanSlug, '/blog', '/'], $this->settings);
            $pingMsg = ' IndexNow notified.';
        }

        ActivityLog::record((int) $user['id'], $id ? 'update' : 'create', 'blog_post', $savedId, $title);
        View::setFlash('success', 'Blog post saved.' . $pingMsg);
        $this->redirect('/admin/blog');
    }

    public function delete(array $params): void
    {
        $user = $this->requireAdmin();
        $this->requirePostCsrf();
        $id = (int) ($params['id'] ?? 0);
        BlogPost::softDelete($id);
        PageCache::invalidateAll();
        ActivityLog::record((int) $user['id'], 'delete', 'blog_post', $id, 'Soft deleted blog post');
        View::setFlash('success', 'Blog post deleted.');
        $this->redirect('/admin/blog');
    }
}
