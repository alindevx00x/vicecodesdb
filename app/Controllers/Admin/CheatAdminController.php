<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\IndexNow;
use App\Core\OgImageGenerator;
use App\Core\PageCache;
use App\Core\Security;
use App\Core\Url;
use App\Core\View;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Cheat;
use App\Models\Tag;

final class CheatAdminController extends Controller
{
    public function index(): void
    {
        $user = $this->requireAdmin();
        $cheats = Cheat::listFiltered([], 200);
        $this->render('admin/cheats/index', [
            'pageTitle' => 'Manage Cheats',
            'adminUser' => $user,
            'cheats'    => $cheats,
        ], 'layouts/admin');
    }

    public function create(): void
    {
        $user = $this->requireAdmin();
        $categories = Category::byType('cheat');
        $this->render('admin/cheats/form', [
            'pageTitle'  => 'Add Cheat Code',
            'adminUser'  => $user,
            'cheat'      => null,
            'categories' => $categories,
            'tagsCsv'    => '',
        ], 'layouts/admin');
    }

    public function edit(array $params): void
    {
        $user = $this->requireAdmin();
        $cheat = Cheat::find((int) ($params['id'] ?? 0));
        if (!$cheat) {
            View::setFlash('error', 'Cheat not found.');
            $this->redirect('/admin/cheats');
        }
        $categories = Category::byType('cheat');
        $tags = Tag::forCheat((int) $cheat['id']);
        $tagsCsv = implode(', ', array_map(static fn($t) => $t['name'], $tags));
        $this->render('admin/cheats/form', [
            'pageTitle'  => 'Edit Cheat: ' . $cheat['title'],
            'adminUser'  => $user,
            'cheat'      => $cheat,
            'categories' => $categories,
            'tagsCsv'    => $tagsCsv,
        ], 'layouts/admin');
    }

    public function save(array $params = []): void
    {
        $user = $this->requireAdmin();
        $this->requirePostCsrf();

        $id = !empty($params['id']) ? (int) $params['id'] : null;
        $existing = $id ? Cheat::find($id) : null;
        $oldStatus = $existing['status'] ?? null;

        $title = trim((string) ($_POST['title'] ?? ''));
        $slug = trim((string) ($_POST['slug'] ?? '')) ?: Url::slugify($title);

        if ($title === '') {
            View::setFlash('error', 'Cheat title is required.');
            $this->redirect($id ? "/admin/cheats/{$id}/edit" : '/admin/cheats/create');
        }

        // Build FAQs JSON
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

        // Build Changelog JSON
        $changelog = [];
        $clDates = $_POST['cl_date'] ?? [];
        $clNotes = $_POST['cl_note'] ?? [];
        for ($i = 0, $n = count($clDates); $i < $n; $i++) {
            $d = trim((string) ($clDates[$i] ?? ''));
            $note = trim((string) ($clNotes[$i] ?? ''));
            if ($d !== '' && $note !== '') {
                $changelog[] = ['date' => $d, 'note' => $note];
            }
        }

        $newStatus = (string) ($_POST['status'] ?? 'coming_soon');
        $cleanSlug = Url::slugify($slug);
        $lastVerifiedAt = trim((string) ($_POST['last_verified_at'] ?? ''));
        if ($newStatus === 'verified' && $oldStatus !== 'verified') {
            $lastVerifiedAt = date('Y-m-d H:i:s');
        } elseif ($lastVerifiedAt === '') {
            $lastVerifiedAt = date('Y-m-d H:i:s');
        }

        $needsRetest = !empty($_POST['needs_retest']);
        if ($newStatus === 'verified' && isset($_POST['clear_retest'])) {
            $needsRetest = false;
        }

        $data = [
            'title'              => $title,
            'slug'               => $cleanSlug,
            'short_description'  => trim((string) ($_POST['short_description'] ?? '')),
            'content'            => Security::sanitizeRichHtml($_POST['content'] ?? ''),
            'effects'            => trim((string) ($_POST['effects'] ?? '')),
            'verification_notes' => trim((string) ($_POST['verification_notes'] ?? '')),
            'category_id'        => $_POST['category_id'] ?? null,
            'status'             => $newStatus,
            'is_featured'        => !empty($_POST['is_featured']),
            'sort_order'         => (int) ($_POST['sort_order'] ?? 0),
            'last_verified_at'   => $lastVerifiedAt,
            'game_patch'         => trim((string) ($_POST['game_patch'] ?? 'v1.00')) ?: 'v1.00',
            'needs_retest'       => $needsRetest ? 1 : 0,
            'meta_title'         => trim((string) ($_POST['meta_title'] ?? '')),
            'meta_description'   => trim((string) ($_POST['meta_description'] ?? '')),
            'focus_keyword'      => trim((string) ($_POST['focus_keyword'] ?? '')),
            'og_image'           => trim((string) ($_POST['og_image'] ?? '')),
            'preview_image'      => trim((string) ($_POST['preview_image'] ?? '')),
            'preview_image_alt'  => trim((string) ($_POST['preview_image_alt'] ?? '')),
            'faqs_json'          => json_encode($faqs, JSON_UNESCAPED_UNICODE),
            'changelog_json'     => json_encode($changelog, JSON_UNESCAPED_UNICODE),
            'platforms'          => $_POST['platforms'] ?? [],
        ];

        $savedId = Cheat::save($data, $id);
        Tag::syncForCheat($savedId, (string) ($_POST['tags'] ?? ''));
        OgImageGenerator::invalidateCheat($cleanSlug);
        PageCache::invalidateAll();

        $pingMsg = '';
        if ($newStatus === 'verified' && $oldStatus !== 'verified') {
            IndexNow::pingUrls(['/cheats/' . $cleanSlug, '/cheats', '/cheats/printable', '/'], $this->settings);
            $pingMsg = ' IndexNow notified.';
        }

        ActivityLog::record((int) $user['id'], $id ? 'update' : 'create', 'cheat', $savedId, $title);
        View::setFlash('success', 'Cheat code saved successfully.' . $pingMsg);
        $this->redirect('/admin/cheats');
    }

    public function bulk(): void
    {
        $user = $this->requireAdmin();
        $this->requirePostCsrf();

        $ids = $_POST['ids'] ?? [];
        $status = (string) ($_POST['bulk_status'] ?? '');
        if (is_array($ids) && $ids !== [] && $status !== '') {
            Cheat::bulkUpdateStatus($ids, $status);
            PageCache::invalidateAll();
            if ($status === 'verified') {
                $urls = ['/cheats', '/cheats/printable', '/'];
                foreach ($ids as $cid) {
                    $c = Cheat::find((int) $cid);
                    if ($c) {
                        OgImageGenerator::invalidateCheat($c['slug']);
                        $urls[] = '/cheats/' . $c['slug'];
                    }
                }
                IndexNow::pingUrls($urls, $this->settings);
            }
            ActivityLog::record((int) $user['id'], 'bulk_status', 'cheat', null, 'Updated ' . count($ids) . ' cheats to ' . $status);
            View::setFlash('success', 'Updated status for ' . count($ids) . ' cheat(s).');
        }
        $this->redirect('/admin/cheats');
    }

    public function quickVerify(array $params): void
    {
        $user = $this->requireAdmin();
        $this->requirePostCsrf();

        $id = (int) ($params['id'] ?? 0);
        $cheat = Cheat::quickVerify($id, (string) ($user['name'] ?? 'Editor'));
        $isAjax = strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest'
            || str_contains(strtolower((string) ($_SERVER['HTTP_ACCEPT'] ?? '')), 'application/json');

        if (!$cheat) {
            if ($isAjax) {
                $this->json(['ok' => false, 'message' => 'Cheat not found.'], 404);
                return;
            }
            View::setFlash('error', 'Cheat not found.');
            $this->redirect('/admin/cheats');
            return;
        }

        OgImageGenerator::invalidateCheat($cheat['slug']);
        PageCache::invalidateAll();
        IndexNow::pingUrls(['/cheats/' . $cheat['slug'], '/cheats', '/cheats/printable', '/'], $this->settings);
        ActivityLog::record((int) $user['id'], 'quick_verify', 'cheat', $id, $cheat['title']);

        if ($isAjax) {
            $this->json([
                'ok'               => true,
                'id'               => $id,
                'status'           => 'verified',
                'last_verified_at' => $cheat['last_verified_at'],
                'message'          => 'Verified & published. Cache flushed and IndexNow notified.',
            ]);
            return;
        }

        View::setFlash('success', '"' . $cheat['title'] . '" verified & published. Cache flushed & IndexNow notified.');
        $this->redirect('/admin/cheats');
    }

    public function delete(array $params): void
    {
        $user = $this->requireAdmin();
        $this->requirePostCsrf();
        $id = (int) ($params['id'] ?? 0);
        Cheat::softDelete($id);
        PageCache::invalidateAll();
        ActivityLog::record((int) $user['id'], 'delete', 'cheat', $id, 'Soft deleted cheat');
        View::setFlash('success', 'Cheat moved to trash.');
        $this->redirect('/admin/cheats');
    }
}
