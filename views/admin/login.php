<?php
use App\Core\Security;
use App\Core\Url;
use App\Core\View;
$flash = View::getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title>Admin Login | ViceCodes</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Atkinson+Hyperlegible:ital,wght@0,400;0,700;1,400&family=Big+Shoulders+Display:wght@700;900&family=Kalam:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= View::e(Url::asset('css/admin.css')) ?>?v=2.0">
</head>
<body class="admin-body" style="display:grid;place-items:center;min-height:100vh;padding:20px;">
  <div class="adm-card" style="width:100%;max-width:420px;padding:30px;">
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;">
      <img src="<?= View::e(Url::asset('img/favicon.svg')) ?>" alt="" width="32" height="32">
      <div>
        <h1 style="margin:0;font-size:26px;line-height:1;">ViceCodes Admin</h1>
        <span style="font-family:var(--f-hand);font-size:13px;color:var(--color-3);">GTA 6 Cheat Codes CMS</span>
      </div>
    </div>

    <?php if ($flash): ?>
      <div class="flash flash-<?= View::e($flash['type']) ?>"><?= View::e($flash['message']) ?></div>
    <?php endif; ?>

    <form action="<?= View::e(Url::to('/admin/login')) ?>" method="post">
      <?= Security::csrfField() ?>
      <div class="field">
        <label for="email">Admin Email</label>
        <input id="email" type="email" name="email" required placeholder="admin@vicecodes.com" autofocus>
      </div>
      <div class="field">
        <label for="password">Password</label>
        <input id="password" type="password" name="password" required placeholder="••••••••">
      </div>
      <?php if (!empty($twoFaEnabled)): ?>
        <div class="field">
          <label for="totp_code">Authenticator 2FA Code</label>
          <input id="totp_code" type="text" name="totp_code" required inputmode="numeric" autocomplete="one-time-code" maxlength="8" placeholder="6-digit code">
        </div>
      <?php endif; ?>
      <button type="submit" class="btn btn-primary" style="width:100%;margin-top:8px;">Sign In</button>
    </form>

    <div style="margin-top:16px;text-align:center;font-size:0.84rem;">
      <a href="<?= View::e(Url::to('/')) ?>">← Back to Public Site</a>
    </div>
  </div>
</body>
</html>
