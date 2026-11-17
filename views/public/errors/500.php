<?php
use App\Core\Url;
use App\Core\View;
?>
<section class="section">
  <div class="container" style="max-width:640px;text-align:center;">
    <div class="paper-card" style="padding:42px 26px;">
      <h1 style="color:var(--color-2);"><?= View::e($errorTitle ?? '500 - Signal Glitch') ?></h1>
      <p class="muted" style="margin:14px 0 24px;"><?= View::e($errorMessage ?? 'Something went wrong. Please refresh shortly.') ?></p>
      <a class="btn btn-primary" href="<?= View::e(Url::to('/')) ?>">Return Home</a>
    </div>
  </div>
</section>
