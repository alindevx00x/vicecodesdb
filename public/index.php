<?php
declare(strict_types=1);

/**
 * ViceCodes - GTA 6 Cheat Codes Front Controller
 */

$config = require dirname(__DIR__) . '/config/app.php';
date_default_timezone_set($config['timezone'] ?? 'UTC');

if ($config['debug']) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', $config['storage_path'] . '/logs/php_errors.log');
}

// Lightweight PSR-4 Autoloader for App\ namespace
spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
    $file = dirname(__DIR__) . '/app/' . $relative . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

use App\Core\Database;
use App\Core\PageCache;
use App\Core\Router;
use App\Core\Security;
use App\Core\Seo;
use App\Core\Url;
use App\Core\View;
use App\Models\Category;
use App\Models\Setting;

Security::sendSecurityHeaders();
Security::startSession();
Url::init($config);

// Fast-path file-based HTML page cache (zero MySQL queries on HIT)
if (PageCache::tryServe()) {
    exit;
}

// Connect DB
$dbConfig = require dirname(__DIR__) . '/config/database.php';
Database::connect($dbConfig);

// Register Routes
$router = new Router();

// SEO / AISEO / Feed / IndexNow Key Routes
$router->get('/sitemap.xml', [\App\Controllers\SeoController::class, 'sitemapIndex']);
$router->get('/sitemap-pages.xml', [\App\Controllers\SeoController::class, 'sitemapPages']);
$router->get('/sitemap-cheats.xml', [\App\Controllers\SeoController::class, 'sitemapCheats']);
$router->get('/sitemap-blog.xml', [\App\Controllers\SeoController::class, 'sitemapBlog']);
$router->get('/robots.txt', [\App\Controllers\SeoController::class, 'robots']);
$router->get('/llms.txt', [\App\Controllers\SeoController::class, 'llmsTxt']);
$router->get('/feed.xml', [\App\Controllers\SeoController::class, 'rssFeed']);
$router->get('/{key}.txt', [\App\Controllers\SeoController::class, 'indexNowKey']);

// Dynamic OpenGraph Images
$router->get('/og/cheat/{slug}.png', [\App\Controllers\CheatController::class, 'ogImage']);

// Public Pages
$router->get('/', [\App\Controllers\HomeController::class, 'index']);
$router->get('/api/cheats.json', [\App\Controllers\CheatController::class, 'apiJson']);
$router->get('/cheats', [\App\Controllers\CheatController::class, 'index']);
$router->get('/cheats/printable', [\App\Controllers\CheatController::class, 'printable']);
$router->get('/cheats/filter', [\App\Controllers\CheatController::class, 'filterAjax']);
$router->get('/cheats/ps5', [\App\Controllers\CheatController::class, 'platform']);
$router->get('/cheats/xbox', [\App\Controllers\CheatController::class, 'platform']);
$router->get('/cheats/pc', [\App\Controllers\CheatController::class, 'platform']);
$router->get('/cheats/weapons', [\App\Controllers\CheatController::class, 'category']);
$router->get('/cheats/health', [\App\Controllers\CheatController::class, 'category']);
$router->get('/cheats/vehicles', [\App\Controllers\CheatController::class, 'category']);
$router->get('/cheats/wanted', [\App\Controllers\CheatController::class, 'category']);
$router->get('/cheats/abilities', [\App\Controllers\CheatController::class, 'category']);
$router->get('/cheats/weather', [\App\Controllers\CheatController::class, 'category']);
$router->get('/cheats/category/{slug}', [\App\Controllers\CheatController::class, 'category']);
$router->post('/cheats/{id}/feedback', [\App\Controllers\CheatController::class, 'feedback']);
$router->get('/cheats/{slug}', [\App\Controllers\CheatController::class, 'show']);

$router->get('/tag/{slug}', [\App\Controllers\TagController::class, 'show']);

$router->get('/blog', [\App\Controllers\BlogController::class, 'index']);
$router->get('/blog/category/{slug}', [\App\Controllers\BlogController::class, 'category']);
$router->get('/blog/{slug}', [\App\Controllers\BlogController::class, 'show']);

$router->get('/search', [\App\Controllers\SearchController::class, 'index']);

$router->post('/newsletter/subscribe', [\App\Controllers\NewsletterController::class, 'subscribe']);
$router->get('/newsletter/confirm', [\App\Controllers\NewsletterController::class, 'confirm']);
$router->get('/newsletter/unsubscribe', [\App\Controllers\NewsletterController::class, 'unsubscribe']);

$router->post('/contact', [\App\Controllers\PageController::class, 'submitContact']);

// Admin Auth
$router->get('/admin/login', [\App\Controllers\Admin\AuthController::class, 'loginForm']);
$router->post('/admin/login', [\App\Controllers\Admin\AuthController::class, 'login']);
$router->get('/admin/logout', [\App\Controllers\Admin\AuthController::class, 'logout']);

// Admin Panel
$router->get('/admin', [\App\Controllers\Admin\DashboardController::class, 'index']);
$router->get('/admin/profile', [\App\Controllers\Admin\AuthController::class, 'profile']);
$router->post('/admin/profile/update', [\App\Controllers\Admin\AuthController::class, 'updateProfile']);
$router->post('/admin/profile/password', [\App\Controllers\Admin\AuthController::class, 'updatePassword']);
$router->get('/admin/cheats', [\App\Controllers\Admin\CheatAdminController::class, 'index']);
$router->get('/admin/cheats/create', [\App\Controllers\Admin\CheatAdminController::class, 'create']);
$router->post('/admin/cheats/store', [\App\Controllers\Admin\CheatAdminController::class, 'save']);
$router->get('/admin/cheats/{id}/edit', [\App\Controllers\Admin\CheatAdminController::class, 'edit']);
$router->post('/admin/cheats/{id}/update', [\App\Controllers\Admin\CheatAdminController::class, 'save']);
$router->post('/admin/cheats/{id}/quick-verify', [\App\Controllers\Admin\CheatAdminController::class, 'quickVerify']);
$router->post('/admin/cheats/{id}/delete', [\App\Controllers\Admin\CheatAdminController::class, 'delete']);
$router->post('/admin/cheats/bulk', [\App\Controllers\Admin\CheatAdminController::class, 'bulk']);

$router->get('/admin/blog', [\App\Controllers\Admin\BlogAdminController::class, 'index']);
$router->get('/admin/blog/create', [\App\Controllers\Admin\BlogAdminController::class, 'create']);
$router->post('/admin/blog/store', [\App\Controllers\Admin\BlogAdminController::class, 'save']);
$router->get('/admin/blog/{id}/edit', [\App\Controllers\Admin\BlogAdminController::class, 'edit']);
$router->post('/admin/blog/{id}/update', [\App\Controllers\Admin\BlogAdminController::class, 'save']);
$router->post('/admin/blog/{id}/delete', [\App\Controllers\Admin\BlogAdminController::class, 'delete']);

$router->get('/admin/pages', [\App\Controllers\Admin\PageAdminController::class, 'index']);
$router->get('/admin/pages/create', [\App\Controllers\Admin\PageAdminController::class, 'create']);
$router->post('/admin/pages/store', [\App\Controllers\Admin\PageAdminController::class, 'save']);
$router->get('/admin/pages/{id}/edit', [\App\Controllers\Admin\PageAdminController::class, 'edit']);
$router->post('/admin/pages/{id}/update', [\App\Controllers\Admin\PageAdminController::class, 'save']);
$router->post('/admin/pages/{id}/delete', [\App\Controllers\Admin\PageAdminController::class, 'delete']);

$router->get('/admin/categories', [\App\Controllers\Admin\CategoryAdminController::class, 'index']);
$router->post('/admin/categories/save', [\App\Controllers\Admin\CategoryAdminController::class, 'save']);
$router->post('/admin/categories/{id}/delete', [\App\Controllers\Admin\CategoryAdminController::class, 'delete']);

$router->get('/admin/newsletter', [\App\Controllers\Admin\NewsletterAdminController::class, 'index']);
$router->get('/admin/newsletter/export', [\App\Controllers\Admin\NewsletterAdminController::class, 'exportCsv']);
$router->post('/admin/newsletter/{id}/status', [\App\Controllers\Admin\NewsletterAdminController::class, 'status']);
$router->post('/admin/newsletter/{id}/delete', [\App\Controllers\Admin\NewsletterAdminController::class, 'delete']);

$router->get('/admin/media', [\App\Controllers\Admin\MediaAdminController::class, 'index']);
$router->get('/admin/media/json', [\App\Controllers\Admin\MediaAdminController::class, 'listJson']);
$router->post('/admin/media/{id}/delete', [\App\Controllers\Admin\MediaAdminController::class, 'delete']);
$router->post('/admin/upload-image', [\App\Controllers\Admin\UploadController::class, 'uploadImage']);

$router->get('/admin/settings', [\App\Controllers\Admin\SettingAdminController::class, 'index']);
$router->post('/admin/settings/save', [\App\Controllers\Admin\SettingAdminController::class, 'save']);
$router->post('/admin/settings/cache-clear', [\App\Controllers\Admin\SettingAdminController::class, 'clearCache']);
$router->post('/admin/contact/{id}/status', [\App\Controllers\Admin\SettingAdminController::class, 'contactStatus']);
$router->post('/admin/contact/{id}/to-cheat', [\App\Controllers\Admin\SettingAdminController::class, 'convertContactToCheat']);

// Static CMS Pages catch-all (slugs like /about, /contact, /privacy-policy, /terms, /disclaimer, /how-we-verify)
$router->get('/{slug}', [\App\Controllers\PageController::class, 'show']);

$capturingCache = PageCache::startCapture();
try {
    $router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', Url::currentPath());
    if ($capturingCache) {
        PageCache::endCaptureAndStore();
    }
} catch (\Throwable $e) {
    if ($capturingCache && ob_get_level() > 0) {
        ob_end_clean();
    }
    error_log('[ViceCodes Error] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    $settings = [];
    $cats = [];
    try {
        $settings = Setting::allKeyed();
        $cats = Category::byType('cheat');
    } catch (\Throwable) {
    }
    View::render('public/errors/500', [
        'seo'             => Seo::buildMeta(['title' => '500 - Server Error | ViceCodes', 'robots' => 'noindex, nofollow'], $settings),
        'settings'        => $settings,
        'cheatCategories' => $cats,
        'errorTitle'      => '500 - Temporary Signal Glitch',
        'errorMessage'    => $config['debug'] ? $e->getMessage() : 'Something went wrong on our end. Please try refreshing in a moment.',
    ]);
}
