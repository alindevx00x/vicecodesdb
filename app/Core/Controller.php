<?php
declare(strict_types=1);

namespace App\Core;

use App\Models\Category;
use App\Models\Setting;
use App\Models\User;

abstract class Controller
{
    protected array $settings = [];
    protected array $cheatCategories = [];

    public function __construct()
    {
        $this->settings = Setting::allKeyed();
        $this->cheatCategories = Category::byType('cheat');

        // Maintenance mode check for public pages (admins bypass)
        $path = Url::currentPath();
        $isAdminRoute = str_starts_with($path, '/admin');
        if (($this->settings['maintenance_mode'] ?? '0') === '1' && !$isAdminRoute && !User::isLoggedIn()) {
            http_response_code(503);
            View::render('public/errors/500', [
                'seo'             => Seo::buildMeta(['title' => 'Maintenance Mode | ViceCodes', 'robots' => 'noindex, nofollow'], $this->settings),
                'settings'        => $this->settings,
                'cheatCategories' => $this->cheatCategories,
                'errorTitle'      => 'Updating Vice City Intel',
                'errorMessage'    => 'We are deploying fresh verified GTA 6 cheat codes. Check back in a moment!',
            ]);
            exit;
        }
    }

    protected function render(string $template, array $data = [], ?string $layout = 'layouts/main'): void
    {
        $data['settings'] = $data['settings'] ?? $this->settings;
        $data['cheatCategories'] = $data['cheatCategories'] ?? $this->cheatCategories;
        $data['flash'] = View::getFlash();
        View::render($template, $data, $layout);
    }

    protected function json(mixed $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . Url::to($path));
        exit;
    }

    protected function requirePostCsrf(): void
    {
        if (!Security::verifyCsrf()) {
            http_response_code(403);
            die('Invalid or expired security token (CSRF). Please refresh and try again.');
        }
    }

    protected function requireAdmin(): array
    {
        $allowlist = trim((string) ($this->settings['admin_ip_allowlist'] ?? ''));
        if ($allowlist !== '' && !Security::isIpAllowed($allowlist)) {
            http_response_code(403);
            die('Access restricted: your IP address is not on the admin allowlist.');
        }
        $user = User::current();
        if (!$user) {
            View::setFlash('error', 'Please sign in to access the admin panel.');
            $this->redirect('/admin/login');
        }
        return $user;
    }
}
