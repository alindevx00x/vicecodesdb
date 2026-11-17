<?php
use App\Core\Url;
use App\Core\View;
?>
<section class="section" style="padding-top:32px;">
  <div class="container">
    <?php require dirname(__DIR__, 1) . '/partials/breadcrumbs.php'; ?>

    <h1>Search Results</h1>
    <form action="<?= View::e(Url::to('/search')) ?>" method="get" style="display:flex;gap:10px;max-width:620px;margin:16px 0 32px;">
      <input class="form-control" type="search" name="q" value="<?= View::e($query) ?>" placeholder="Search GTA 6 cheats, vehicles, weapons, guides...">
      <button class="btn btn-primary" type="submit">Search</button>
    </form>

    <?php if ($query === ''): ?>
      <p style="color:var(--text-muted);">Type at least 2 characters above to search across all GTA 6 cheat codes and guides.</p>
    <?php else: ?>
      <h2>Matching Cheats (<?= count($cheats) ?>)</h2>
      <?php if ($cheats === []): ?>
        <p style="color:var(--text-muted);margin-bottom:28px;">No cheats matched "<?= View::e($query) ?>".</p>
      <?php else: ?>
        <div class="grid-cheats" style="margin-bottom:36px;">
          <?php foreach ($cheats as $cheat): ?>
            <?php require dirname(__DIR__, 1) . '/partials/cheat-card.php'; ?>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <h2>Matching Guides &amp; Articles (<?= count($posts) ?>)</h2>
      <?php if ($posts === []): ?>
        <p style="color:var(--text-muted);">No blog guides matched "<?= View::e($query) ?>".</p>
      <?php else: ?>
        <div class="grid-blog">
          <?php foreach ($posts as $post): ?>
            <?php require dirname(__DIR__, 1) . '/partials/blog-card.php'; ?>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</section>
