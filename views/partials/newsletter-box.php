<?php
use App\Core\Security;
use App\Core\Url;
use App\Core\View;
?>
<section class="newsletter-panel" id="newsletter">
  <div>
    <span class="eyebrow">Launch alert dispatch</span>
    <h2>Get every new cheat the hour it's verified</h2>
    <p style="color:var(--color-7);margin:0;">
      One email when GTA 6 codes drop, then a short weekly digest. No spam, unsubscribe anytime.
    </p>
  </div>

  <div>
    <form class="js-newsletter-form" action="<?= View::e(Url::to('/newsletter/subscribe')) ?>" method="post">
      <?= Security::csrfField() ?>
      <div class="newsletter-fields">
        <input type="text" name="name" placeholder="First name (optional)" aria-label="First name">
        <input type="email" name="email" placeholder="you@example.com" required aria-label="Email address">
        <button type="submit" class="btn btn-primary">Notify Me</button>
      </div>
      <label class="gdpr-check">
        <input type="checkbox" name="gdpr_consent" value="1" required>
        <span>You'll get a confirmation email first. See our <a href="<?= View::e(Url::to('/privacy-policy')) ?>">privacy policy</a>.</span>
      </label>
      <div class="js-newsletter-msg"></div>
    </form>
  </div>
</section>
