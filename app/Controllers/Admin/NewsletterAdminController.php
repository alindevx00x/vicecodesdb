<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\View;
use App\Models\Newsletter;

final class NewsletterAdminController extends Controller
{
    public function index(): void
    {
        $user = $this->requireAdmin();
        $subscribers = Newsletter::all();
        $this->render('admin/newsletter/index', [
            'pageTitle'   => 'Newsletter Subscribers',
            'adminUser'   => $user,
            'subscribers' => $subscribers,
        ], 'layouts/admin');
    }

    public function status(array $params): void
    {
        $this->requireAdmin();
        $this->requirePostCsrf();
        $id = (int) ($params['id'] ?? 0);
        $status = (string) ($_POST['status'] ?? 'subscribed');
        Newsletter::updateStatus($id, $status);
        View::setFlash('success', 'Subscriber status updated.');
        $this->redirect('/admin/newsletter');
    }

    public function delete(array $params): void
    {
        $this->requireAdmin();
        $this->requirePostCsrf();
        $id = (int) ($params['id'] ?? 0);
        Newsletter::delete($id);
        View::setFlash('success', 'Subscriber deleted.');
        $this->redirect('/admin/newsletter');
    }

    public function exportCsv(): void
    {
        $this->requireAdmin();
        $subscribers = Newsletter::all();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="vicecodes-subscribers-' . date('Y-m-d') . '.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['ID', 'Email', 'Name', 'Status', 'Confirmed At', 'Created At']);
        foreach ($subscribers as $sub) {
            fputcsv($out, [
                $sub['id'],
                $sub['email'],
                $sub['name'],
                $sub['status'],
                $sub['confirmed_at'],
                $sub['created_at'],
            ]);
        }
        fclose($out);
        exit;
    }
}
