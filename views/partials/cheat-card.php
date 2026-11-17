<?php
use App\Core\Url;
use App\Core\View;

$badge = View::statusBadge($cheat['status'] ?? 'coming_soon');
$platforms = $cheat['platforms'] ?? [];
$defaultPlat = (!empty($activePlatform) && isset($platforms[$activePlatform]))
    ? $activePlatform
    : (isset($platforms['ps5']) ? 'ps5' : (array_key_first($platforms) ?: 'ps5'));
$cardClasses = !empty($isPinCard) ? 'pin-card cheat-card' : 'paper-card cheat-card';
$patchVer = trim((string) ($cheat['game_patch'] ?? 'v1.00')) ?: 'v1.00';

$loadoutPayload = [
    'slug'     => $cheat['slug'],
    'title'    => $cheat['title'],
    'url'      => Url::to('/cheats/' . $cheat['slug']),
    'patch'    => $patchVer,
    'ps5'      => $platforms['ps5']['code_sequence'] ?? '',
    'xbox'     => $platforms['xbox']['code_sequence'] ?? '',
    'pc'       => $platforms['pc']['code_sequence'] ?? '',
    'phone'    => $platforms['ps5']['cell_number'] ?? ($platforms['xbox']['cell_number'] ?? ($platforms['pc']['cell_number'] ?? '')),
];
?>
<article class="<?= $cardClasses ?>" data-cheat-card data-cheat-slug="<?= View::e($cheat['slug']) ?>">
  <?php if (!empty($isPinCard)): ?>
    <span class="pin-tack" aria-hidden="true"></span>
  <?php endif; ?>

  <div class="cheat-card-top">
    <div>
      <?php if (!empty($cheat['category_name'])): ?>
        <a class="cheat-cat-tag" href="<?= View::e(Url::to('/cheats/category/' . $cheat['category_slug'])) ?>">
          <?= View::e($cheat['category_name']) ?>
        </a>
      <?php endif; ?>
      <h3><a href="<?= View::e(Url::to('/cheats/' . $cheat['slug'])) ?>"><?= View::e($cheat['title']) ?></a></h3>
    </div>
    <div style="display:flex;align-items:center;gap:6px;">
      <button
        type="button"
        class="fav-pin-btn"
        data-loadout-toggle="<?= View::e(json_encode($loadoutPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?>"
        title="Save to My Quick Cheats"
        aria-label="Save <?= View::e($cheat['title']) ?> to My Quick Cheats"
        aria-pressed="false">★</button>
      <span class="badge <?= View::e($badge['class']) ?>"><?= View::e($badge['icon'] . ' ' . $badge['label']) ?></span>
    </div>
  </div>

  <p class="cheat-desc"><?= View::e($cheat['short_description']) ?></p>

  <?php if (!empty($platforms)): ?>
    <div class="plat-tabs" role="tablist">
      <?php foreach ($platforms as $pKey => $pRow): ?>
        <button
          type="button"
          class="plat-tab <?= $pKey === $defaultPlat ? 'active' : '' ?>"
          data-plat="<?= View::e($pKey) ?>"
          role="tab">
          <?= View::e(strtoupper($pKey === 'xbox' ? 'Xbox X|S' : $pKey)) ?>
        </button>
      <?php endforeach; ?>
    </div>

    <?php foreach ($platforms as $pKey => $pRow): ?>
      <div class="code-box plat-code-pane" data-plat-pane="<?= View::e($pKey) ?>" style="<?= $pKey === $defaultPlat ? '' : 'display:none;' ?>">
        <div class="code-value">
          <?= View::formatButtonSequence((string) $pRow['code_sequence'], $pKey) ?>
          <?php if (!empty($pRow['cell_number'])): ?>
            <div style="font-size:12px;color:var(--color-2);margin-top:4px;">Phone: <strong><?= View::e($pRow['cell_number']) ?></strong></div>
          <?php endif; ?>
        </div>
        <button
          type="button"
          class="copy-btn"
          data-copy-text="<?= View::e($pRow['code_sequence']) ?>"
          aria-label="Copy <?= View::e($cheat['title']) ?> code">
          Copy
        </button>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <div class="code-box">
      <span class="code-placeholder">Coming at launch (Nov 19, 2026)</span>
    </div>
  <?php endif; ?>

  <div class="cheat-card-footer">
    <span>
      <?= ($cheat['status'] ?? '') === 'verified' ? 'Verified on Patch ' : 'Patch ' ?><strong><?= View::e($patchVer) ?></strong> ·
      <?= View::e(date('M j, Y', strtotime((string) ($cheat['last_verified_at'] ?? $cheat['updated_at'])))) ?>
    </span>
    <a href="<?= View::e(Url::to('/cheats/' . $cheat['slug'])) ?>">Open Case →</a>
  </div>
</article>
