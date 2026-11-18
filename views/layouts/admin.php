<?php
use App\Core\Security;
use App\Core\Url;
use App\Core\View;

$currentPath = Url::currentPath();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title><?= View::e($pageTitle ?? 'Admin') ?> | ViceCodes Admin</title>
  <link rel="icon" type="image/svg+xml" href="<?= View::e(Url::asset('img/favicon.svg')) ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Atkinson+Hyperlegible:ital,wght@0,400;0,700;1,400&family=Big+Shoulders+Display:wght@700;900&family=Kalam:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= View::e(Url::asset('css/admin.css')) ?>?v=2.1">
  <!-- TinyMCE Self-Hosted Free Community Version via cdnjs (No API Key Required) -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/7.6.0/tinymce.min.js" referrerpolicy="origin"></script>
  <script>
    window.VC_ADMIN = {
      uploadUrl: <?= json_encode(Url::to('/admin/upload-image')) ?>,
      mediaJsonUrl: <?= json_encode(Url::to('/admin/media/json')) ?>,
      csrfToken: <?= json_encode(Security::csrfToken()) ?>
    };
  </script>
</head>
<body class="admin-body">
  <div class="adm-shell">
    <aside class="adm-sidebar">
      <div class="adm-brand">
        <img src="<?= View::e(Url::asset('img/favicon.svg')) ?>" alt="" width="28" height="28">
        <span>ViceCodes CMS</span>
      </div>

      <nav class="adm-nav">
        <a href="<?= View::e(Url::to('/admin')) ?>" class="<?= $currentPath === '/admin' ? 'active' : '' ?>">Dashboard</a>
        <a href="<?= View::e(Url::to('/admin/cheats')) ?>" class="<?= str_starts_with($currentPath, '/admin/cheats') ? 'active' : '' ?>">Cheat Codes</a>
        <a href="<?= View::e(Url::to('/admin/blog')) ?>" class="<?= str_starts_with($currentPath, '/admin/blog') ? 'active' : '' ?>">Blog Posts</a>
        <a href="<?= View::e(Url::to('/admin/pages')) ?>" class="<?= str_starts_with($currentPath, '/admin/pages') ? 'active' : '' ?>">Static Pages</a>
        <a href="<?= View::e(Url::to('/admin/categories')) ?>" class="<?= str_starts_with($currentPath, '/admin/categories') ? 'active' : '' ?>">Categories</a>
        <a href="<?= View::e(Url::to('/admin/newsletter')) ?>" class="<?= str_starts_with($currentPath, '/admin/newsletter') ? 'active' : '' ?>">Newsletter</a>
        <a href="<?= View::e(Url::to('/admin/media')) ?>" class="<?= str_starts_with($currentPath, '/admin/media') ? 'active' : '' ?>">Media Library</a>
        <a href="<?= View::e(Url::to('/admin/badges')) ?>" class="<?= str_starts_with($currentPath, '/admin/badges') ? 'active' : '' ?>">Trust Badges</a>
        <a href="<?= View::e(Url::to('/admin/settings')) ?>" class="<?= str_starts_with($currentPath, '/admin/settings') ? 'active' : '' ?>">Site Settings</a>
        <a href="<?= View::e(Url::to('/admin/profile')) ?>" class="<?= str_starts_with($currentPath, '/admin/profile') ? 'active' : '' ?>">Profile &amp; Password</a>
      </nav>

      <div style="margin-top:auto;padding-top:14px;border-top:1px solid var(--color-6);font-size:13px;color:var(--color-3);">
        <div>Signed in as <strong style="color:var(--color-1);"><?= View::e($adminUser['name'] ?? 'Admin') ?></strong></div>
        <div style="display:flex;gap:12px;margin-top:8px;">
          <a href="<?= View::e(Url::to('/')) ?>" target="_blank">View Site ↗</a>
          <a href="<?= View::e(Url::to('/admin/logout')) ?>" style="color:var(--color-2);font-weight:700;">Logout</a>
        </div>
      </div>
    </aside>

    <main class="adm-main">
      <div class="adm-topbar">
        <h1><?= View::e($pageTitle ?? 'Dashboard') ?></h1>
        <div style="display:flex;gap:8px;">
          <a class="btn btn-secondary btn-sm" href="<?= View::e(Url::to('/admin/cheats/create')) ?>">+ New Cheat</a>
          <a class="btn btn-primary btn-sm" href="<?= View::e(Url::to('/admin/blog/create')) ?>">+ New Post</a>
        </div>
      </div>

      <?php if (!empty($flash)): ?>
        <div class="flash flash-<?= View::e($flash['type']) ?>"><?= View::e(strip_tags((string) $flash['message'])) ?></div>
      <?php endif; ?>

      <?= $content ?>
    </main>
  </div>

  <!-- Inline Media Library Picker Modal -->
  <div id="vc-media-modal" class="adm-modal" hidden aria-modal="true" role="dialog" aria-labelledby="vc-media-modal-title">
    <div class="adm-modal-backdrop" data-close-media-modal></div>
    <div class="adm-modal-dialog">
      <div class="adm-modal-head">
        <h3 id="vc-media-modal-title" style="margin:0;">Choose From Media Library</h3>
        <button type="button" class="btn btn-secondary btn-sm" data-close-media-modal>✕ Close</button>
      </div>
      <div id="vc-media-modal-grid" class="adm-modal-grid">
        <p style="color:var(--adm-muted);">Loading uploaded images...</p>
      </div>
    </div>
  </div>

  <script src="<?= View::e(Url::asset('js/admin.js')) ?>?v=2.3"></script>
</body>
</html>
