<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\PageCache;
use App\Core\View;
use App\Models\ActivityLog;
use App\Models\BlogPost;
use App\Models\Cheat;
use App\Models\Newsletter;
use App\Models\Setting;

final class DashboardController extends Controller
{
    public function index(): void
    {
        $user = $this->requireAdmin();
        $statusCounts = Cheat::statusCounts();
        $totalPosts = BlogPost::countAll();
        $subsCount = Newsletter::countSubscribed();
        $totalViews = (int) Database::pdo()->query('SELECT COALESCE(SUM(views),0) FROM cheats WHERE deleted_at IS NULL')->fetchColumn()
                    + (int) Database::pdo()->query('SELECT COALESCE(SUM(views),0) FROM blog_posts WHERE deleted_at IS NULL')->fetchColumn();
        $unreadContacts = (int) Database::pdo()->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'new'")->fetchColumn();
        $recentLogs = ActivityLog::recent(10);
        $recentPosts = BlogPost::published([], 5);
        $retestCheats = Cheat::flaggedForRetest();
        $cacheCount = count(glob(PageCache::cacheDir() . '/*.html') ?: []);
        $settings = Setting::allKeyed();
        $extraVideos = json_decode((string) ($settings['video_extra_json'] ?? '[]'), true);
        if (!is_array($extraVideos)) {
            $extraVideos = [];
        }

        $this->render('admin/dashboard', [
            'pageTitle'      => 'Dashboard',
            'adminUser'      => $user,
            'statusCounts'   => $statusCounts,
            'totalPosts'     => $totalPosts,
            'subsCount'      => $subsCount,
            'totalViews'     => $totalViews,
            'unreadContacts' => $unreadContacts,
            'recentLogs'     => $recentLogs,
            'recentPosts'    => $recentPosts,
            'retestCheats'   => $retestCheats,
            'cacheCount'     => $cacheCount,
            'settings'       => $settings,
            'extraVideos'    => $extraVideos,
        ], 'layouts/admin');
    }

    public function saveVideos(): void
    {
        $user = $this->requireAdmin();
        $this->requirePostCsrf();

        $featuredUrl = trim((string) ($_POST['video_featured_url'] ?? ''));
        if ($featuredUrl !== '' && View::extractYoutubeId($featuredUrl) === null) {
            View::setFlash('error', 'Featured YouTube URL is invalid. Paste a standard YouTube watch, live, shorts, or youtu.be link.');
            $this->redirect('/admin');
            return;
        }

        $extraTitles = $_POST['extra_title'] ?? [];
        $extraUrls   = $_POST['extra_url'] ?? [];
        $extraLives  = $_POST['extra_live'] ?? [];
        $extras = [];

        if (is_array($extraUrls)) {
            foreach ($extraUrls as $idx => $rawUrl) {
                $url = trim((string) $rawUrl);
                if ($url === '') {
                    continue;
                }
                if (View::extractYoutubeId($url) === null) {
                    continue;
                }
                $extras[] = [
                    'title'   => trim((string) ($extraTitles[$idx] ?? '')) ?: 'GTA 6 Video Update',
                    'url'     => $url,
                    'is_live' => !empty($extraLives[$idx]),
                ];
            }
        }

        Setting::setMany([
            'video_section_enabled'  => !empty($_POST['video_section_enabled']) ? '1' : '0',
            'video_is_live'          => !empty($_POST['video_is_live']) ? '1' : '0',
            'video_section_title'    => trim((string) ($_POST['video_section_title'] ?? 'GTA 6 Live Stream & Watch Desk')) ?: 'GTA 6 Live Stream & Watch Desk',
            'video_section_subtitle' => trim((string) ($_POST['video_section_subtitle'] ?? '')),
            'video_featured_url'     => $featuredUrl,
            'video_featured_title'   => trim((string) ($_POST['video_featured_title'] ?? '')),
            'video_featured_note'    => trim((string) ($_POST['video_featured_note'] ?? '')),
            'video_extra_json'       => json_encode($extras, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        PageCache::invalidateAll();
        ActivityLog::record((int) $user['id'], 'update', 'homepage_videos', null, 'Updated homepage livestream & YouTube video hub');
        View::setFlash('success', 'Homepage Livestream & YouTube videos updated and published!');
        $this->redirect('/admin');
    }
}
