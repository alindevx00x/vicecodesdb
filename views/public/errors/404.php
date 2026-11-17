<?php
use App\Core\Url;
use App\Core\View;
?>
<section class="section">
  <div class="container" style="max-width:640px;text-align:center;">
    <div class="paper-card" style="padding:42px 26px;">
      <div style="font-family:var(--f-display);font-weight:900;font-size:78px;line-height:1;color:var(--color-4);">404</div>
      <h1 style="margin:10px 0;">Lost in the Leonida Keys</h1>
      <p class="muted" style="margin-bottom:24px;">That page doesn't exist or has been relocated. Jump back to our live GTA 6 cheat directory.</p>
      <div style="display:flex;justify-content:center;gap:12px;flex-wrap:wrap;">
        <a class="btn btn-primary" href="<?= View::e(Url::to('/')) ?>">Back to Home</a>
        <a class="btn btn-outline" href="<?= View::e(Url::to('/cheats')) ?>">All Cheats</a>
      </div>
    </div>
  </div>
</section>
