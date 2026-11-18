<?php
use App\Core\Url;
use App\Core\View;
?>
<section class="section" style="padding-top:32px;">
  <div class="container">
    <?php require dirname(__DIR__, 2) . '/partials/breadcrumbs.php'; ?>

    <div class="section-header">
      <div>
        <h1><?= View::e($pageHeading ?? 'All GTA 6 Cheat Codes') ?></h1>
        <p class="section-sub"><?= View::e($pageIntro ?? '') ?></p>
      </div>
      <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
        <a class="btn btn-secondary btn-sm" href="<?= View::e(Url::to('/cheats/printable')) ?>">Printable Cheat Sheet</a>
        <span id="cheats-results-count" class="badge badge-coming"><?= count($cheats) ?> cheat<?= count($cheats) === 1 ? '' : 's' ?> found</span>
      </div>
    </div>

    <?php if (!empty($sharedLoadout)): ?>
      <div class="paper-card" style="padding:18px 20px;margin-bottom:22px;border-left:4px solid var(--color-5);background:var(--bg-surface);">
        <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;margin-bottom:12px;">
          <div>
            <span class="eyebrow">Shared Loadout Link</span>
            <h2 style="margin:2px 0 0;font-size:24px;">Custom Quick Cheats (<?= count($sharedLoadout) ?>)</h2>
          </div>
          <button
            type="button"
            class="btn btn-primary btn-sm"
            id="vc-import-shared-loadout"
            data-shared-slugs="<?= View::e(implode(',', array_map(static fn($s) => $s['slug'], $sharedLoadout))) ?>">
            Save All to My Quick Cheats
          </button>
        </div>
        <div class="grid-cheats">
          <?php foreach ($sharedLoadout as $cheat): ?>
            <?php require dirname(__DIR__, 2) . '/partials/cheat-card.php'; ?>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

    <!-- Platform & Category Filter Matrix -->
    <div class="platform-pills" style="margin-bottom:10px;">
      <a class="platform-pill" data-global-plat="" href="<?= View::e(Url::to('/cheats')) ?>" style="<?= empty($activePlatform) ? 'border-color:var(--color-1);background:var(--color-1);color:var(--color-11);' : '' ?>">All Platforms</a>
      <a class="platform-pill" data-global-plat="ps5" href="<?= View::e(Url::to('/cheats/ps5')) ?>" style="<?= ($activePlatform ?? '') === 'ps5' ? 'border-color:var(--color-1);background:var(--color-1);color:var(--color-11);' : '' ?>">PS5</a>
      <a class="platform-pill" data-global-plat="xbox" href="<?= View::e(Url::to('/cheats/xbox')) ?>" style="<?= ($activePlatform ?? '') === 'xbox' ? 'border-color:var(--color-1);background:var(--color-1);color:var(--color-11);' : '' ?>">Xbox Series X|S</a>
      <a class="platform-pill" data-global-plat="pc" href="<?= View::e(Url::to('/cheats/pc')) ?>" style="<?= ($activePlatform ?? '') === 'pc' ? 'border-color:var(--color-1);background:var(--color-1);color:var(--color-11);' : '' ?>">PC</a>
    </div>
    <div class="platform-pills" style="margin-bottom:20px;">
      <?php
      $catShortcuts = [
          'weapons-combat'   => ['label' => 'Weapons', 'path' => '/cheats/weapons'],
          'health-armor'     => ['label' => 'Health & Armor', 'path' => '/cheats/health'],
          'vehicles-spawns'  => ['label' => 'Vehicles', 'path' => '/cheats/vehicles'],
          'wanted-level'     => ['label' => 'Wanted Level', 'path' => '/cheats/wanted'],
          'player-abilities' => ['label' => 'Abilities', 'path' => '/cheats/abilities'],
          'weather-world'    => ['label' => 'Weather & World', 'path' => '/cheats/weather'],
      ];
      foreach ($catShortcuts as $catSlug => $sc):
          $isAct = ($activeCategory['slug'] ?? '') === $catSlug;
      ?>
        <a class="platform-pill" href="<?= View::e(Url::to($sc['path'])) ?>" style="<?= $isAct ? 'border-color:var(--color-2);background:var(--color-2);color:#fff;' : '' ?>"><?= View::e($sc['label']) ?></a>
      <?php endforeach; ?>
    </div>

    <!-- AJAX Filter Bar -->
    <form id="cheat-filter-form" class="glass-card filter-bar" data-ajax-url="<?= View::e(Url::to('/cheats/filter')) ?>" method="get" action="<?= View::e(Url::to('/cheats')) ?>">
      <div class="filter-group">
        <label for="f-q">Keyword Search</label>
        <input id="f-q" type="search" name="q" placeholder="Search god mode, car, weapons..." value="<?= View::e($filters['q'] ?? '') ?>">
      </div>

      <div class="filter-group">
        <label for="f-plat">Platform</label>
        <select id="f-plat" name="platform">
          <option value="">All Platforms</option>
          <option value="ps5" <?= ($filters['platform'] ?? '') === 'ps5' ? 'selected' : '' ?>>PS5</option>
          <option value="xbox" <?= ($filters['platform'] ?? '') === 'xbox' ? 'selected' : '' ?>>Xbox Series X|S</option>
          <option value="pc" <?= ($filters['platform'] ?? '') === 'pc' ? 'selected' : '' ?>>PC</option>
        </select>
      </div>

      <div class="filter-group">
        <label for="f-cat">Category</label>
        <select id="f-cat" name="category">
          <option value="">All Categories</option>
          <?php foreach ($cheatCategories as $cat): ?>
            <option value="<?= View::e($cat['slug']) ?>" <?= ($filters['category_slug'] ?? '') === $cat['slug'] ? 'selected' : '' ?>>
              <?= View::e($cat['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="filter-group">
        <label for="f-status">Verification</label>
        <select id="f-status" name="status">
          <option value="">All Statuses</option>
          <option value="verified" <?= ($filters['status'] ?? '') === 'verified' ? 'selected' : '' ?>>Verified Only</option>
          <option value="coming_soon" <?= ($filters['status'] ?? '') === 'coming_soon' ? 'selected' : '' ?>>Coming at Launch</option>
          <option value="unconfirmed" <?= ($filters['status'] ?? '') === 'unconfirmed' ? 'selected' : '' ?>>Unconfirmed</option>
          <option value="debunked" <?= ($filters['status'] ?? '') === 'debunked' ? 'selected' : '' ?>>Debunked</option>
        </select>
      </div>

      <div class="filter-group">
        <label for="f-sort">Sort By</label>
        <select id="f-sort" name="sort">
          <option value="order" <?= ($filters['sort'] ?? '') === 'order' ? 'selected' : '' ?>>Recommended</option>
          <option value="popular" <?= ($filters['sort'] ?? '') === 'popular' ? 'selected' : '' ?>>Most Popular</option>
          <option value="newest" <?= ($filters['sort'] ?? '') === 'newest' ? 'selected' : '' ?>>Recently Checked</option>
          <option value="title" <?= ($filters['sort'] ?? '') === 'title' ? 'selected' : '' ?>>A–Z</option>
        </select>
      </div>
    </form>

    <div id="cheats-results-grid" class="grid-cheats">
      <?php if ($cheats === []): ?>
        <div class="empty-state glass-card">
          <h3>No cheats matched your current filters</h3>
          <p style="color:var(--text-muted);">Try resetting your filter options or check back at launch on November 19, 2026.</p>
          <a class="btn btn-primary btn-sm" href="<?= View::e(Url::to('/cheats')) ?>">Reset Filters</a>
        </div>
      <?php else: ?>
        <?php foreach ($cheats as $cheat): ?>
          <?php require dirname(__DIR__, 2) . '/partials/cheat-card.php'; ?>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</section>
