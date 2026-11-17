<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\PageCache;
use App\Core\Security;
use App\Core\Url;
use App\Core\View;
use App\Models\ActivityLog;
use App\Models\Cheat;
use App\Models\Setting;

final class SettingAdminController extends Controller
{
    public function index(): void
    {
        $user = $this->requireAdmin();
        $filterStatus = $_GET['inbox'] ?? '';
        if (in_array($filterStatus, ['new', 'read', 'replied', 'archived'], true)) {
            $st = Database::pdo()->prepare('SELECT * FROM contact_messages WHERE status = :st ORDER BY created_at DESC LIMIT 50');
            $st->execute([':st' => $filterStatus]);
            $messages = $st->fetchAll();
        } else {
            $messages = Database::pdo()->query("SELECT * FROM contact_messages WHERE status != 'archived' ORDER BY created_at DESC LIMIT 50")->fetchAll();
        }
        $settings = Setting::allKeyed();
        if (empty($settings['admin_2fa_secret'])) {
            $settings['admin_2fa_secret_preview'] = Security::generateTotpSecret();
        }
        $cacheFiles = glob(PageCache::cacheDir() . '/*.html') ?: [];

        $this->render('admin/settings/index', [
            'pageTitle'    => 'Site Settings & Inbox',
            'adminUser'    => $user,
            'settings'     => $settings,
            'messages'     => $messages,
            'inboxFilter'  => $filterStatus,
            'cacheCount'   => count($cacheFiles),
        ], 'layouts/admin');
    }

    public function save(): void
    {
        $user = $this->requireAdmin();
        $this->requirePostCsrf();

        $allowed = [
            'site_name', 'site_tagline', 'launch_date', 'launch_mode',
            'launch_banner_text', 'default_meta_title', 'default_meta_description',
            'ga4_id', 'plausible_domain', 'social_x', 'social_youtube',
            'social_discord', 'contact_email', 'footer_disclaimer',
            'indexnow_key', 'mail_driver', 'mail_from', 'mail_api_key',
            'smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass', 'smtp_encryption',
            'admin_2fa_secret', 'admin_ip_allowlist',
        ];

        $updates = [];
        foreach ($allowed as $key) {
            if (isset($_POST[$key])) {
                $updates[$key] = trim((string) $_POST[$key]);
            }
        }
        $updates['maintenance_mode']   = !empty($_POST['maintenance_mode']) ? '1' : '0';
        $updates['indexnow_enabled']   = !empty($_POST['indexnow_enabled']) ? '1' : '0';
        $updates['page_cache_enabled'] = !empty($_POST['page_cache_enabled']) ? '1' : '0';
        $updates['admin_2fa_enabled']  = !empty($_POST['admin_2fa_enabled']) ? '1' : '0';

        if ($updates['admin_2fa_enabled'] === '1' && empty($updates['admin_2fa_secret'])) {
            $updates['admin_2fa_secret'] = Security::generateTotpSecret();
        }

        Setting::setMany($updates);
        PageCache::setEnabledFlag($updates['page_cache_enabled'] === '1');
        PageCache::invalidateAll();

        ActivityLog::record((int) $user['id'], 'update', 'settings', null, 'Updated site settings');
        View::setFlash('success', 'Site settings saved & page cache cleared.');
        $this->redirect('/admin/settings');
    }

    public function clearCache(): void
    {
        $user = $this->requireAdmin();
        $this->requirePostCsrf();
        PageCache::invalidateAll();
        ActivityLog::record((int) $user['id'], 'cache_clear', 'cache', null, 'Flushed HTML page cache');
        View::setFlash('success', 'Page cache flushed successfully.');
        $this->redirect('/admin/settings');
    }

    public function contactStatus(array $params): void
    {
        $user = $this->requireAdmin();
        $this->requirePostCsrf();
        $id = (int) ($params['id'] ?? 0);
        $status = (string) ($_POST['status'] ?? 'read');
        if (in_array($status, ['new', 'read', 'replied', 'archived'], true) && $id > 0) {
            $stmt = Database::pdo()->prepare('UPDATE contact_messages SET status = :st WHERE id = :id');
            $stmt->execute([':st' => $status, ':id' => $id]);
            ActivityLog::record((int) $user['id'], 'contact_status', 'contact_message', $id, "Marked contact #{$id} as {$status}");
            View::setFlash('success', "Submission #{$id} marked as {$status}.");
        }
        $this->redirect('/admin/settings');
    }

    public function convertContactToCheat(array $params): void
    {
        $user = $this->requireAdmin();
        $this->requirePostCsrf();
        $id = (int) ($params['id'] ?? 0);

        $stmt = Database::pdo()->prepare('SELECT * FROM contact_messages WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $msg = $stmt->fetch();
        if (!$msg) {
            View::setFlash('error', 'Contact submission not found.');
            $this->redirect('/admin/settings');
        }

        $title = mb_substr(trim((string) $msg['subject']), 0, 140) ?: 'Community Cheat Submission #' . $id;
        $slug = Url::slugify($title) . '-' . $id;

        $cheatId = Cheat::save([
            'title'              => $title,
            'slug'               => $slug,
            'short_description'  => mb_substr(trim(preg_replace('/\s+/', ' ', (string) $msg['message'])), 0, 260),
            'content'            => '<p>Submitted via contact form by ' . View::e($msg['name']) . ':</p><blockquote>' . nl2br(View::e($msg['message'])) . '</blockquote>',
            'verification_notes' => 'Converted from contact submission #' . $id . '. Pending verification.',
            'status'             => 'unconfirmed',
            'is_featured'        => false,
            'game_patch'         => 'v1.00',
            'platforms'          => [
                'ps5' => ['code_sequence' => 'UNCONFIRMED - Pending Verification', 'notes' => 'Community tip #' . $id],
            ],
        ]);

        Database::pdo()->prepare("UPDATE contact_messages SET status = 'read' WHERE id = :id")->execute([':id' => $id]);
        ActivityLog::record((int) $user['id'], 'convert_contact', 'cheat', $cheatId, "Converted contact #{$id} to draft cheat");
        View::setFlash('success', 'Created draft cheat from contact tip. Review and edit inputs below.');
        $this->redirect("/admin/cheats/{$cheatId}/edit");
    }
}
