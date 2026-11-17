<?php
use App\Core\Security;
use App\Core\Url;
use App\Core\View;
?>
<section class="section" style="padding-top:32px;">
  <div class="container" style="max-width:780px;">
    <?php require dirname(__DIR__, 2) . '/partials/breadcrumbs.php'; ?>

    <h1><?= View::e($page['title']) ?></h1>
    <div class="prose" style="margin-bottom:20px;color:var(--text-muted);"><?= $page['content'] ?></div>

    <div class="glass-card" style="padding:26px;">
      <form action="<?= View::e(Url::to('/contact')) ?>" method="post" style="display:grid;gap:14px;">
        <?= Security::csrfField() ?>
        <div>
          <label for="c-name" style="display:block;font-weight:700;margin-bottom:6px;">Your Name</label>
          <input id="c-name" class="form-control" type="text" name="name" required placeholder="Jason / Lucia">
        </div>
        <div>
          <label for="c-email" style="display:block;font-weight:700;margin-bottom:6px;">Email Address</label>
          <input id="c-email" class="form-control" type="email" name="email" required placeholder="you@example.com">
        </div>
        <div>
          <label for="c-subj" style="display:block;font-weight:700;margin-bottom:6px;">Subject</label>
          <input id="c-subj" class="form-control" type="text" name="subject" required placeholder="New PS5 Cheat Code / Correction">
        </div>
        <div>
          <label for="c-msg" style="display:block;font-weight:700;margin-bottom:6px;">Message or Button Sequence</label>
          <textarea id="c-msg" class="form-control" name="message" rows="5" required placeholder="Share platform, exact button inputs, and how you tested it..."></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Send Message</button>
      </form>
    </div>
  </div>
</section>
