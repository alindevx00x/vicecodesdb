<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\PageCache;
use App\Models\ActivityLog;
use App\Models\BlogPost;
use App\Models\Cheat;
use App\Models\Newsletter;

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
        ], 'layouts/admin');
    }
}
