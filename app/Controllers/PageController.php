<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Security;
use App\Core\Seo;
use App\Core\Url;
use App\Core\View;
use App\Models\Page;

final class PageController extends Controller
{
    public function show(array $params): void
    {
        $slug = (string) ($params['slug'] ?? ltrim(Url::currentPath(), '/'));
        $page = Page::findBySlug($slug);
        if (!$page) {
            $this->notFound();
            return;
        }

        $breadcrumbs = [
            ['label' => 'Home', 'url' => '/'],
            ['label' => $page['title'], 'url' => '/' . $page['slug']],
        ];

        $seo = Seo::buildMeta([
            'title'       => $page['meta_title'] ?: ($page['title'] . ' | ViceCodes'),
            'description' => $page['meta_description'] ?: ($page['quick_answer'] ?? ''),
            'keywords'    => $page['focus_keyword'] ?? 'ViceCodes, GTA 6 cheats',
            'schemas'     => [Seo::breadcrumbSchema($breadcrumbs)],
        ], $this->settings);

        $template = $slug === 'contact' ? 'public/pages/contact' : 'public/pages/show';
        $this->render($template, [
            'seo'         => $seo,
            'page'        => $page,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    public function submitContact(): void
    {
        $this->requirePostCsrf();

        if (!Security::checkRateLimit('contact_form', 5, 600)) {
            View::setFlash('error', 'Too many messages sent recently. Please wait a few minutes.');
            $this->redirect('/contact');
        }

        $name = trim((string) ($_POST['name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $subject = trim((string) ($_POST['subject'] ?? ''));
        $message = trim((string) ($_POST['message'] ?? ''));

        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $subject === '' || strlen($message) < 10) {
            View::setFlash('error', 'Please fill out all fields with a valid email and at least 10 characters in your message.');
            $this->redirect('/contact');
        }

        $stmt = Database::pdo()->prepare(
            'INSERT INTO contact_messages (name, email, subject, message, ip_address) VALUES (:n, :e, :s, :m, :ip)'
        );
        $stmt->execute([
            ':n'  => mb_substr($name, 0, 120),
            ':e'  => mb_substr($email, 0, 191),
            ':s'  => mb_substr($subject, 0, 191),
            ':m'  => $message,
            ':ip' => Security::clientIp(),
        ]);

        View::setFlash('success', 'Thanks! Your message has been sent to our verification editors.');
        $this->redirect('/contact');
    }

    public function notFound(): void
    {
        http_response_code(404);
        $seo = Seo::buildMeta([
            'title'  => '404 - Page Not Found | ViceCodes',
            'robots' => 'noindex, follow',
        ], $this->settings);

        $this->render('public/errors/404', ['seo' => $seo]);
    }
}
