<?php
use App\Core\View;
?>
<section class="section" style="padding-top:32px;">
  <div class="container" style="max-width:860px;">
    <?php require dirname(__DIR__, 2) . '/partials/breadcrumbs.php'; ?>

    <h1><?= View::e($page['title']) ?></h1>

    <?php if (!empty($page['quick_answer'])): ?>
      <div class="quick-answer-box">
        <div class="quick-answer-label">Summary</div>
        <p style="margin:0;"><?= View::e($page['quick_answer']) ?></p>
      </div>
    <?php endif; ?>

    <div class="glass-card prose" style="padding:26px;">
      <?= $page['content'] ?>
    </div>
  </div>
</section>
