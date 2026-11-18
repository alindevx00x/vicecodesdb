<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Security;
use App\Core\View;
use App\Models\ActivityLog;
use App\Models\User;

final class AuthController extends Controller
{
    public function loginForm(): void
    {
        $allowlist = trim((string) ($this->settings['admin_ip_allowlist'] ?? ''));
        if ($allowlist !== '' && !Security::isIpAllowed($allowlist)) {
            http_response_code(403);
            die('Access restricted: your IP address is not on the admin allowlist.');
        }
        if (User::isLoggedIn()) {
            $this->redirect('/admin');
        }
        $this->render('admin/login', [
            'pageTitle'  => 'Admin Sign In',
            'twoFaEnabled' => ($this->settings['admin_2fa_enabled'] ?? '0') === '1' && !empty($this->settings['admin_2fa_secret']),
        ], null);
    }

    public function login(): void
    {
        $allowlist = trim((string) ($this->settings['admin_ip_allowlist'] ?? ''));
        if ($allowlist !== '' && !Security::isIpAllowed($allowlist)) {
            http_response_code(403);
            die('Access restricted: your IP address is not on the admin allowlist.');
        }

        $this->requirePostCsrf();

        if (!Security::checkRateLimit('admin_login', 8, 300)) {
            View::setFlash('error', 'Too many login attempts. Please wait 5 minutes.');
            $this->redirect('/admin/login');
        }

        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        $twoFaEnabled = ($this->settings['admin_2fa_enabled'] ?? '0') === '1' && !empty($this->settings['admin_2fa_secret']);
        if ($twoFaEnabled) {
            $totpCode = trim((string) ($_POST['totp_code'] ?? ''));
            if (!Security::verifyTotp((string) $this->settings['admin_2fa_secret'], $totpCode)) {
                View::setFlash('error', 'Invalid 2FA authenticator code.');
                $this->redirect('/admin/login');
            }
        }

        $user = User::attemptLogin($email, $password);
        if (!$user) {
            View::setFlash('error', 'Invalid email or password.');
            $this->redirect('/admin/login');
        }

        ActivityLog::record((int) $user['id'], 'login', 'user', (int) $user['id'], 'Admin signed in');
        View::setFlash('success', 'Welcome back, ' . $user['name'] . '!');
        $this->redirect('/admin');
    }

    public function logout(): void
    {
        $user = User::current();
        if ($user) {
            ActivityLog::record((int) $user['id'], 'logout', 'user', (int) $user['id'], 'Admin signed out');
        }
        User::logout();
        View::setFlash('success', 'Signed out securely.');
        $this->redirect('/admin/login');
    }

    public function profile(): void
    {
        $user = $this->requireAdmin();
        $this->render('admin/profile', [
            'pageTitle' => 'Account Profile & Password',
            'adminUser' => $user,
        ], 'layouts/admin');
    }

    public function updateProfile(): void
    {
        $user = $this->requireAdmin();
        $this->requirePostCsrf();

        $name = trim((string) ($_POST['name'] ?? ''));
        $bio = trim((string) ($_POST['bio'] ?? ''));
        if ($name === '') {
            View::setFlash('error', 'Display name is required.');
            $this->redirect('/admin/profile');
            return;
        }

        User::updateProfile((int) $user['id'], $name, $bio);
        ActivityLog::record((int) $user['id'], 'update_profile', 'user', (int) $user['id'], 'Updated admin profile');
        View::setFlash('success', 'Profile details updated.');
        $this->redirect('/admin/profile');
    }

    public function updatePassword(): void
    {
        $user = $this->requireAdmin();
        $this->requirePostCsrf();

        $current = (string) ($_POST['current_password'] ?? '');
        $next    = (string) ($_POST['new_password'] ?? '');
        $confirm = (string) ($_POST['confirm_password'] ?? '');

        if ($current === '' || !User::verifyPassword((int) $user['id'], $current)) {
            View::setFlash('error', 'Current password is incorrect.');
            $this->redirect('/admin/profile');
            return;
        }
        if (strlen($next) < 8) {
            View::setFlash('error', 'New password must be at least 8 characters long.');
            $this->redirect('/admin/profile');
            return;
        }
        if (!hash_equals($next, $confirm)) {
            View::setFlash('error', 'New password and confirmation do not match.');
            $this->redirect('/admin/profile');
            return;
        }

        User::updatePassword((int) $user['id'], $next);
        ActivityLog::record((int) $user['id'], 'change_password', 'user', (int) $user['id'], 'Changed admin password');
        View::setFlash('success', 'Password updated successfully.');
        $this->redirect('/admin/profile');
    }
}
