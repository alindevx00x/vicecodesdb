<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\PageCache;
use App\Core\Security;
use App\Core\Url;
use App\Core\View;
use App\Models\ActivityLog;
use App\Models\Page;

final class PageAdminController extends Controller
{
    public function index(): void
    {
        $user = $this->requireAdmin();
        $pages = Page::all();
        $this->render('admin/pages/index', [
            'pageTitle' => 'Static Pages',
            'adminUser' => $user,
            'pages'     => $pages,
        ], 'layouts/admin');
    }

    public function create(): void
    {
        $user = $this->requireAdmin();
        $this->render('admin/pages/form', [
            'pageTitle' => 'Create Static Page',
            'adminUser' => $user,
            'page'      => null,
        ], 'layouts/admin');
    }

    public function edit(array $params): void
    {
        $user = $this->requireAdmin();
        $page = Page::find((int) ($params['id'] ?? 0));
        if (!$page) {
            View::setFlash('error', 'Page not found.');
            $this->redirect('/admin/pages');
        }
        $this->render('admin/pages/form', [
            'pageTitle' => 'Edit Page: ' . $page['title'],
            'adminUser' => $user,
            'page'      => $page,
        ], 'layouts/admin');
    }

    public function save(array $params = []): void
    {
        $user = $this->requireAdmin();
        $this->requirePostCsrf();

        $id = !empty($params['id']) ? (int) $params['id'] : null;
        $title = trim((string) ($_POST['title'] ?? ''));
        $slug = trim((string) ($_POST['slug'] ?? '')) ?: Url::slugify($title);

        if ($title === '') {
            View::setFlash('error', 'Page title is required.');
            $this->redirect($id ? "/admin/pages/{$id}/edit" : '/admin/pages/create');
        }

        $savedId = Page::save([
            'title'            => $title,
            'slug'             => Url::slugify($slug),
            'quick_answer'     => trim((string) ($_POST['quick_answer'] ?? '')),
            'content'          => Security::sanitizeRichHtml($_POST['content'] ?? ''),
            'meta_title'       => trim((string) ($_POST['meta_title'] ?? '')),
            'meta_description' => trim((string) ($_POST['meta_description'] ?? '')),
            'focus_keyword'    => trim((string) ($_POST['focus_keyword'] ?? '')),
            'status'           => $_POST['status'] ?? 'published',
        ], $id);

        PageCache::invalidateAll();
        ActivityLog::record((int) $user['id'], $id ? 'update' : 'create', 'page', $savedId, $title);
        View::setFlash('success', 'Static page saved.');
        $this->redirect('/admin/pages');
    }

    public function delete(array $params): void
    {
        $user = $this->requireAdmin();
        $this->requirePostCsrf();
        $id = (int) ($params['id'] ?? 0);
        Page::delete($id);
        PageCache::invalidateAll();
        ActivityLog::record((int) $user['id'], 'delete', 'page', $id, 'Deleted static page');
        View::setFlash('success', 'Page deleted.');
        $this->redirect('/admin/pages');
    }
}
