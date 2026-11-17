<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\View;
use App\Models\Media;

final class MediaAdminController extends Controller
{
    public function index(): void
    {
        $user = $this->requireAdmin();
        $items = Media::all();
        $this->render('admin/media/index', [
            'pageTitle' => 'Media Library',
            'adminUser' => $user,
            'items'     => $items,
        ], 'layouts/admin');
    }

    public function listJson(): void
    {
        $this->requireAdmin();
        $this->json(['items' => Media::all()]);
    }

    public function delete(array $params): void
    {
        $this->requireAdmin();
        $this->requirePostCsrf();
        Media::delete((int) ($params['id'] ?? 0));
        View::setFlash('success', 'Media item deleted.');
        $this->redirect('/admin/media');
    }
}
