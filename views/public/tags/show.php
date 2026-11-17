<?php
use App\Core\Url;
use App\Core\View;
?>
<section class="section" style="padding-top:32px;">
  <div class="container">
    <?php require dirname(__DIR__, 2) . '/partials/breadcrumbs.php'; ?>

    <div class="section-header">
      <div>
        <span class="eyebrow">Topical Tag Archive</span>
        <h1>#<?= View::e($tag['name']) ?> <em class="text-accent">GTA 6 Cheats &amp; Guides</em></h1>
        <p class="section-sub">All verified cheat sequences and editorial case files grouped under <?= View::e($tag['name']) ?>.</p>
      </div>
      <a class="btn btn-secondary btn-sm" href="<?= View::e(Url::to('/cheats/printable')) ?>">Printable Cheat Sheet</a>
    </div>

    <?php if (!empty($allTags)): ?>
      <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:26px;">
        <?php foreach ($allTags as $t): ?>
          <a class="tag-pill <?= $t['slug'] === $tag['slug'] ? 'active' : '' ?>" href="<?= View::e(Url::to('/tag/' . $t['slug'])) ?>">
            #<?= View::e($t['name']) ?> (<?= (int) $t['cheat_count'] + (int) $t['post_count'] ?>)
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($cheats)): ?>
      <h2 style="margin-bottom:14px;">Cheat Codes (<?= count($cheats) ?>)</h2>
      <div class="grid-cheats" style="margin-bottom:36px;">
        <?php foreach ($cheats as $cheat): ?>
          <?php require dirname(__DIR__, 2) . '/partials/cheat-card.php'; ?>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($posts)): ?>
      <h2 style="margin-bottom:14px;">Related Case Files &amp; Guides (<?= count($posts) ?>)</h2>
      <div class="grid-blog">
        <?php foreach ($posts as $post): ?>
          <?php require dirname(__DIR__, 2) . '/partials/blog-card.php'; ?>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if (empty($cheats) && empty($posts)): ?>
      <div class="paper-card empty-state">
        <h3>No entries tagged yet</h3>
        <p class="muted">Browse all tracked GTA 6 cheat codes or check back at launch.</p>
        <a class="btn btn-primary btn-sm" href="<?= View::e(Url::to('/cheats')) ?>">Browse All Cheats</a>
      </div>
    <?php endif; ?>
  </div>
</section>
