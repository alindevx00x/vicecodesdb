<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\PageCache;
use App\Core\Url;
use App\Core\View;
use App\Models\ActivityLog;
use App\Models\Category;

final class CategoryAdminController extends Controller
{
    public function index(): void
    {
        $user = $this->requireAdmin();
        $categories = Category::all();
        $this->render('admin/categories/index', [
            'pageTitle'  => 'Categories',
            'adminUser'  => $user,
            'categories' => $categories,
        ], 'layouts/admin');
    }

    public function save(): void
    {
        $user = $this->requireAdmin();
        $this->requirePostCsrf();

        $id = !empty($_POST['id']) ? (int) $_POST['id'] : null;
        $name = trim((string) ($_POST['name'] ?? ''));
        $slug = trim((string) ($_POST['slug'] ?? '')) ?: Url::slugify($name);

        if ($name === '') {
            View::setFlash('error', 'Category name is required.');
            $this->redirect('/admin/categories');
        }

        $payload = [
            'name'             => $name,
            'slug'             => Url::slugify($slug),
            'type'             => in_array($_POST['type'] ?? '', ['cheat', 'blog'], true) ? $_POST['type'] : 'cheat',
            'icon'             => trim((string) ($_POST['icon'] ?? 'bolt')),
            'description'      => trim((string) ($_POST['description'] ?? '')),
            'meta_title'       => trim((string) ($_POST['meta_title'] ?? '')),
            'meta_description' => trim((string) ($_POST['meta_description'] ?? '')),
            'sort_order'       => (int) ($_POST['sort_order'] ?? 0),
        ];

        if ($id) {
            Category::update($id, $payload);
            ActivityLog::record((int) $user['id'], 'update', 'category', $id, $name);
        } else {
            $newId = Category::create($payload);
            ActivityLog::record((int) $user['id'], 'create', 'category', $newId, $name);
        }

        PageCache::invalidateAll();
        View::setFlash('success', 'Category saved.');
        $this->redirect('/admin/categories');
    }

    public function delete(array $params): void
    {
        $user = $this->requireAdmin();
        $this->requirePostCsrf();
        $id = (int) ($params['id'] ?? 0);
        Category::delete($id);
        PageCache::invalidateAll();
        ActivityLog::record((int) $user['id'], 'delete', 'category', $id, 'Deleted category');
        View::setFlash('success', 'Category deleted.');
        $this->redirect('/admin/categories');
    }
}
