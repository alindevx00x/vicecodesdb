<?php
use App\Core\Url;
use App\Core\View;
?>
<article class="paper-card blog-card">
  <a class="blog-thumb" href="<?= View::e(Url::to('/blog/' . $post['slug'])) ?>">
    <?php if (!empty($post['featured_image'])): ?>
      <img src="<?= View::e(Url::to($post['featured_image'])) ?>" alt="<?= View::e($post['featured_image_alt'] ?: $post['title']) ?>" loading="lazy">
    <?php else: ?>
      <img src="<?= View::e(Url::asset('img/vice-hero.jpg')) ?>" alt="<?= View::e($post['title']) ?>" loading="lazy" style="filter:grayscale(0.35) sepia(0.18);">
    <?php endif; ?>
  </a>
  <div class="blog-body">
    <div class="blog-meta">
      <?php if (!empty($post['category_name'])): ?>
        <span style="color:var(--color-2);font-weight:700;"><?= View::e($post['category_name']) ?></span> ·
      <?php endif; ?>
      <span><?= View::e(date('M j, Y', strtotime((string) ($post['published_at'] ?? $post['created_at'])))) ?></span> ·
      <span><?= (int) ($post['reading_time'] ?? 3) ?> min read</span>
    </div>
    <h3><a href="<?= View::e(Url::to('/blog/' . $post['slug'])) ?>"><?= View::e($post['title']) ?></a></h3>
    <p class="muted" style="font-size:14px;margin:0;"><?= View::e($post['excerpt'] ?? '') ?></p>
    <div style="margin-top:auto;padding-top:8px;">
      <a href="<?= View::e(Url::to('/blog/' . $post['slug'])) ?>" style="font-family:var(--f-display);font-weight:700;font-size:16px;text-transform:uppercase;text-decoration:none;">Read File →</a>
    </div>
  </div>
</article>
