<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Security;
use App\Core\Seo;
use App\Core\Url;
use App\Core\View;
use App\Models\Newsletter;

final class NewsletterController extends Controller
{
    public function subscribe(): void
    {
        $this->requirePostCsrf();

        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        if (!Security::checkRateLimit('newsletter_sub', 6, 600)) {
            $msg = 'Too many subscription attempts. Please try again in a few minutes.';
            if ($isAjax) {
                $this->json(['ok' => false, 'message' => $msg], 429);
            }
            View::setFlash('error', $msg);
            $this->redirect('/');
        }

        $email = trim((string) ($_POST['email'] ?? ''));
        $name = trim((string) ($_POST['name'] ?? '')) ?: null;
        $consent = !empty($_POST['gdpr_consent']);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $msg = 'Please enter a valid email address.';
            if ($isAjax) {
                $this->json(['ok' => false, 'message' => $msg], 422);
            }
            View::setFlash('error', $msg);
            $this->redirect('/');
        }

        if (!$consent) {
            $msg = 'Please check the GDPR consent box to receive GTA 6 cheat alerts.';
            if ($isAjax) {
                $this->json(['ok' => false, 'message' => $msg], 422);
            }
            View::setFlash('error', $msg);
            $this->redirect('/');
        }

        $result = Newsletter::subscribe($email, $name, Security::clientIp());

        if ($result['state'] === 'already_subscribed') {
            $msg = 'You are already confirmed for GTA 6 launch cheat alerts!';
            if ($isAjax) {
                $this->json(['ok' => true, 'message' => $msg]);
            }
            View::setFlash('success', $msg);
            $this->redirect('/');
        }

        $confirmUrl = Url::full('/newsletter/confirm?token=' . urlencode($result['token']));
        $unsubUrl = Url::full('/newsletter/unsubscribe?token=' . urlencode($result['token']));

        $htmlEmail = '<div style="font-family:Arial,sans-serif;max-width:560px;margin:0 auto;padding:24px;background:#FAF6F0;color:#1B1C1D;border:1px solid #D6CAB8;">'
            . '<h2 style="margin-top:0;color:#1B1C1D;">Confirm Your ViceCodes Launch Alert</h2>'
            . '<p>Click the button below to confirm your email for verified GTA 6 PS5, Xbox Series X|S, and PC cheat code alerts.</p>'
            . '<p style="margin:24px 0;"><a href="' . View::e($confirmUrl) . '" style="background:#B0281F;color:#FAF6F0;padding:12px 22px;text-decoration:none;font-weight:bold;border-radius:6px;display:inline-block;">Confirm Subscription</a></p>'
            . '<p style="font-size:12px;color:#7A7368;">Or paste this link: ' . View::e($confirmUrl) . '<br>Unsubscribe anytime: ' . View::e($unsubUrl) . '</p></div>';
        $textEmail = "Confirm your GTA 6 Cheat Codes Launch Alert subscription:\n{$confirmUrl}\n\nUnsubscribe anytime:\n{$unsubUrl}";

        \App\Core\Mailer::send(
            $email,
            'Confirm your GTA 6 Cheat Codes Launch Alert subscription',
            $htmlEmail,
            $textEmail,
            $this->settings
        );

        $msg = 'Almost done! Confirm your subscription using the verification link sent to your email.';
        $driver = strtolower((string) ($this->settings['mail_driver'] ?? 'log'));
        if ($driver === 'log' || ($_ENV['APP_ENV'] ?? 'local') !== 'production') {
            $msg .= ' (Local Dev Preview: <a href="' . View::e($confirmUrl) . '" style="text-decoration:underline;color:#B0281F;">Click here to verify email</a>)';
        }

        if ($isAjax) {
            $this->json(['ok' => true, 'message' => $msg, 'confirm_url' => $confirmUrl]);
        }

        View::setFlash('success', $msg);
        $this->redirect('/');
    }

    public function confirm(): void
    {
        $token = trim((string) ($_GET['token'] ?? ''));
        $ok = $token !== '' && Newsletter::confirmByToken($token);

        $seo = Seo::buildMeta([
            'title'  => 'Newsletter Confirmation | ViceCodes',
            'robots' => 'noindex, follow',
        ], $this->settings);

        $this->render('public/newsletter/status', [
            'seo'     => $seo,
            'ok'      => $ok,
            'mode'    => 'confirm',
            'heading' => $ok ? 'Subscription Confirmed!' : 'Invalid or Expired Link',
            'message' => $ok
                ? 'You\'re on the VIP launch list. We\'ll alert you the moment verified GTA 6 PS5 & Xbox cheat codes go live.'
                : 'This confirmation token is invalid or has already been used.',
        ]);
    }

    public function unsubscribe(): void
    {
        $token = trim((string) ($_GET['token'] ?? ''));
        $ok = $token !== '' && Newsletter::unsubscribeByToken($token);

        $seo = Seo::buildMeta([
            'title'  => 'Unsubscribed | ViceCodes',
            'robots' => 'noindex, nofollow',
        ], $this->settings);

        $this->render('public/newsletter/status', [
            'seo'     => $seo,
            'ok'      => $ok,
            'mode'    => 'unsubscribe',
            'heading' => $ok ? 'You Have Been Unsubscribed' : 'Link Not Found',
            'message' => $ok
                ? 'Your email has been unsubscribed from ViceCodes GTA 6 alerts. You can re-subscribe anytime.'
                : 'We could not find an active subscription matching that link.',
        ]);
    }
}
