<?php
use App\Core\Url;
use App\Core\View;
?>
<section class="section">
  <div class="container" style="max-width:640px;">
    <div class="glass-card" style="padding:36px;text-align:center;">
      <span class="badge <?= $ok ? 'badge-verified' : 'badge-debunked' ?>" style="margin-bottom:14px;">
        <?= $ok ? 'Confirmed' : 'Notice' ?>
      </span>
      <h1><?= View::e($heading) ?></h1>
      <p style="color:var(--text-muted);margin:14px 0 24px;"><?= View::e($message) ?></p>
      <a class="btn btn-primary" href="<?= View::e(Url::to('/cheats')) ?>">Browse GTA 6 Cheats</a>
    </div>
  </div>
</section>
