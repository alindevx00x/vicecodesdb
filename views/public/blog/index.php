<?php
use App\Core\Url;
use App\Core\View;
?>
<section class="section" style="padding-top:32px;">
  <div class="container">
    <?php require dirname(__DIR__, 2) . '/partials/breadcrumbs.php'; ?>

    <div class="section-header">
      <div>
        <h1><?= isset($activeCategory) ? View::e($activeCategory['name']) : 'GTA 6 Guides &amp; Launch Intel' ?></h1>
        <p class="section-sub">Honest launch coverage, input tutorials, and verification logs from the ViceCodes editorial desk.</p>
      </div>
    </div>

    <div class="platform-pills" style="margin-bottom:24px;">
      <a class="platform-pill" href="<?= View::e(Url::to('/blog')) ?>" style="<?= empty($activeCat) ? 'border-color:var(--color-1);background:var(--color-1);color:var(--color-11);' : '' ?>">All Posts</a>
      <?php foreach ($blogCategories as $bc): ?>
        <a class="platform-pill" href="<?= View::e(Url::to('/blog/category/' . $bc['slug'])) ?>" style="<?= ($activeCat ?? '') === $bc['slug'] ? 'border-color:var(--color-1);background:var(--color-1);color:var(--color-11);' : '' ?>">
          <?= View::e($bc['name']) ?>
        </a>
      <?php endforeach; ?>
    </div>

    <div class="grid-blog">
      <?php foreach ($posts as $post): ?>
        <?php require dirname(__DIR__, 2) . '/partials/blog-card.php'; ?>
      <?php endforeach; ?>
    </div>
  </div>
</section>
