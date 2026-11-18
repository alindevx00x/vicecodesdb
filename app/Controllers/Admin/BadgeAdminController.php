<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\PageCache;
use App\Core\View;
use App\Models\ActivityLog;
use App\Models\Setting;
use App\Models\TrustBadge;

final class BadgeAdminController extends Controller
{
    public function index(): void
    {
        $user = $this->requireAdmin();
        $badges = TrustBadge::all();
        $settings = Setting::allKeyed();

        $this->render('admin/badges/index', [
            'pageTitle' => 'Trust & Recognition Badges',
            'adminUser' => $user,
            'badges'    => $badges,
            'settings'  => $settings,
        ], 'layouts/admin');
    }

    public function saveSettings(): void
    {
        $user = $this->requireAdmin();
        $this->requirePostCsrf();

        $speed = max(20, min(400, (int) ($_POST['badges_speed'] ?? 100)));
        $bgStyle = in_array($_POST['badges_bg_style'] ?? 'dark', ['dark', 'light'], true)
            ? (string) $_POST['badges_bg_style']
            : 'dark';

        Setting::setMany([
            'badges_enabled'    => !empty($_POST['badges_enabled']) ? '1' : '0',
            'badges_heading'    => trim((string) ($_POST['badges_heading'] ?? 'Recognition & Trust')) ?: 'Recognition & Trust',
            'badges_subheading' => trim((string) ($_POST['badges_subheading'] ?? 'Our product has gained industry recognition')),
            'badges_speed'      => (string) $speed,
            'badges_bg_style'   => $bgStyle,
        ]);

        PageCache::invalidateAll();
        ActivityLog::record((int) $user['id'], 'update', 'trust_badges_settings', null, 'Updated footer badge carousel settings');
        View::setFlash('success', 'Badge carousel settings saved & page cache cleared.');
        $this->redirect('/admin/badges');
    }

    public function store(): void
    {
        $user = $this->requireAdmin();
        $this->requirePostCsrf();

        $rawHtml = trim((string) ($_POST['badge_html'] ?? ''));
        $label = trim((string) ($_POST['label'] ?? ''));
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);
        $isActive = !empty($_POST['is_active']);

        if ($rawHtml === '' || TrustBadge::sanitizeBadgeHtml($rawHtml) === '') {
            View::setFlash('error', 'Please paste valid badge HTML code (e.g. <a href="..."><img src="..." /></a>).');
            $this->redirect('/admin/badges');
            return;
        }

        $id = TrustBadge::create($label, $rawHtml, $sortOrder, $isActive);
        PageCache::invalidateAll();
        ActivityLog::record((int) $user['id'], 'create', 'trust_badge', $id, $label !== '' ? $label : 'Added trust badge #' . $id);
        View::setFlash('success', 'Trust badge added to the footer carousel.');
        $this->redirect('/admin/badges');
    }

    public function update(array $params): void
    {
        $user = $this->requireAdmin();
        $this->requirePostCsrf();

        $id = (int) ($params['id'] ?? 0);
        $existing = TrustBadge::find($id);
        if (!$existing) {
            View::setFlash('error', 'Badge not found.');
            $this->redirect('/admin/badges');
            return;
        }

        $rawHtml = trim((string) ($_POST['badge_html'] ?? ''));
        if ($rawHtml === '' || TrustBadge::sanitizeBadgeHtml($rawHtml) === '') {
            View::setFlash('error', 'Badge HTML code cannot be empty.');
            $this->redirect('/admin/badges');
            return;
        }

        $label = trim((string) ($_POST['label'] ?? ''));
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);
        $isActive = !empty($_POST['is_active']);

        TrustBadge::update($id, $label, $rawHtml, $sortOrder, $isActive);
        PageCache::invalidateAll();
        ActivityLog::record((int) $user['id'], 'update', 'trust_badge', $id, $label !== '' ? $label : 'Updated badge #' . $id);
        View::setFlash('success', 'Badge #' . $id . ' updated.');
        $this->redirect('/admin/badges');
    }

    public function toggle(array $params): void
    {
        $user = $this->requireAdmin();
        $this->requirePostCsrf();

        $id = (int) ($params['id'] ?? 0);
        TrustBadge::toggleActive($id);
        PageCache::invalidateAll();
        ActivityLog::record((int) $user['id'], 'toggle', 'trust_badge', $id, 'Toggled trust badge visibility');
        View::setFlash('success', 'Badge visibility updated.');
        $this->redirect('/admin/badges');
    }

    public function delete(array $params): void
    {
        $user = $this->requireAdmin();
        $this->requirePostCsrf();

        $id = (int) ($params['id'] ?? 0);
        TrustBadge::delete($id);
        PageCache::invalidateAll();
        ActivityLog::record((int) $user['id'], 'delete', 'trust_badge', $id, 'Deleted trust badge #' . $id);
        View::setFlash('success', 'Badge removed from footer carousel.');
        $this->redirect('/admin/badges');
    }
}
