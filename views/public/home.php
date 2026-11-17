<?php
use App\Core\Url;
use App\Core\View;

$launchMode = $settings['launch_mode'] ?? 'pre_launch';
$launchDate = $settings['launch_date'] ?? '2026-11-19T00:00:00Z';
?>

<!-- 1. Dark Editorial Case-File Hero Slab -->
<section class="hero">
  <div class="hero-bg" style="background-image: url('<?= View::e(Url::asset('img/vice-hero.jpg')) ?>');" aria-hidden="true"></div>
  <div class="hero-overlay" aria-hidden="true"></div>

  <div class="container hero-content">
    <div>
      <span class="case-stamp">
        <?= $launchMode === 'launched' ? 'CASE FILE // CODES LIVE' : 'CASE FILE // LAUNCH WATCH' ?>
      </span>
      <h1>GTA 6 <em class="text-accent">Cheat Codes</em></h1>
      <p class="hero-subtitle">
        Every GTA 6 cheat code for PS5, Xbox Series X|S, and PC. Confirmed inputs, rumored leaks, and debunked scams clearly labelled.
      </p>

      <div class="hero-ctas">
        <a href="<?= View::e(Url::to('/cheats')) ?>" class="btn btn-primary">Open the Database →</a>
        <a href="<?= View::e(Url::to('/how-we-verify')) ?>" class="btn btn-ghost-light">How We Verify</a>
      </div>

      <div class="platform-pills" aria-label="Filter by platform">
        <a class="platform-pill" href="<?= View::e(Url::to('/cheats/ps5')) ?>">PS5 DualSense</a>
        <a class="platform-pill" href="<?= View::e(Url::to('/cheats/xbox')) ?>">Xbox Series X|S</a>
        <a class="platform-pill" href="<?= View::e(Url::to('/cheats/pc')) ?>">PC Tracker</a>
      </div>
    </div>

    <!-- Dossier Tracker / Countdown Card -->
    <aside class="countdown-panel" aria-label="Launch status tracker">
      <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;">
        <span class="eyebrow" style="margin:0;">
          <?= $launchMode === 'launched' ? 'Live verification desk' : 'Release countdown' ?>
        </span>
        <span class="badge <?= ($statusCounts['verified'] ?? 0) > 0 ? 'badge-verified' : 'badge-coming' ?>">
          <?= (int) ($statusCounts['verified'] ?? 0) ?> Verified
        </span>
      </div>

      <?php if ($launchMode !== 'launched'): ?>
        <div class="countdown-grid" id="launch-countdown" data-launch-date="<?= View::e($launchDate) ?>">
          <div class="cd-box"><span class="cd-num" data-cd="days">02</span><span class="cd-lbl">Days</span></div>
          <div class="cd-box"><span class="cd-num" data-cd="hours">00</span><span class="cd-lbl">Hrs</span></div>
          <div class="cd-box"><span class="cd-num" data-cd="mins">00</span><span class="cd-lbl">Min</span></div>
          <div class="cd-box"><span class="cd-num" data-cd="secs">00</span><span class="cd-lbl">Sec</span></div>
        </div>
        <p style="font-size:13px;color:var(--text-secondary);margin:0 0 12px;">
          Official launch: <strong>November 19, 2026</strong>. No official Rockstar codes published yet.
        </p>
      <?php else: ?>
        <p style="font-size:14px;color:var(--text-primary);margin:12px 0;">
          GTA 6 is live! Editors are actively testing and timestamping Story Mode codes on PS5 and Xbox Series X|S.
        </p>
      <?php endif; ?>

      <div class="stat-mini-row">
        <div>
          <span class="stat-mini-val"><?= (int) ($statusCounts['total'] ?? 0) ?></span>
          <span class="stat-mini-lbl">Tracked</span>
        </div>
        <div>
          <span class="stat-mini-val" style="color:var(--color-5);"><?= (int) ($statusCounts['unconfirmed'] ?? 0) ?></span>
          <span class="stat-mini-lbl">Unconfirmed</span>
        </div>
        <div>
          <span class="stat-mini-val" style="color:var(--color-2);"><?= (int) ($statusCounts['debunked'] ?? 0) ?></span>
          <span class="stat-mini-lbl">Debunked</span>
        </div>
      </div>
    </aside>
  </div>
</section>

<div class="stripes" aria-hidden="true"></div>

<!-- 2. Pinned to the Board (Corkboard Featured Cheats) -->
<section class="section">
  <div class="container">
    <div class="section-header">
      <div>
        <span class="eyebrow">01 · Active dossier</span>
        <h2>Pinned to the <em class="text-accent">Board</em></h2>
        <p class="section-sub">Most requested GTA 6 codes and rumor checks with one-click copy and verification labels.</p>
      </div>
      <a class="btn btn-outline btn-sm" href="<?= View::e(Url::to('/cheats')) ?>">All <?= (int) ($statusCounts['total'] ?? 0) ?> Cheats →</a>
    </div>

    <div class="board-wrap">
      <div class="pins-grid">
        <?php foreach ($featuredCheats as $cheat): ?>
          <?php $isPinCard = true; require dirname(__DIR__) . '/partials/cheat-card.php'; ?>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- 3-Column Platform Summary Strip -->
    <div class="platform-summary-grid">
      <a class="platform-summary-card" href="<?= View::e(Url::to('/cheats/ps5')) ?>">
        <span class="platform-summary-num">PS5</span>
        <div>
          <strong style="font-family:var(--f-display);font-size:20px;text-transform:uppercase;display:block;">DualSense Inputs</strong>
          <span class="muted" style="font-size:13px;">Button combos &amp; phone dial list</span>
        </div>
      </a>
      <a class="platform-summary-card" href="<?= View::e(Url::to('/cheats/xbox')) ?>">
        <span class="platform-summary-num">XBX</span>
        <div>
          <strong style="font-family:var(--f-display);font-size:20px;text-transform:uppercase;display:block;">Xbox Series X|S</strong>
          <span class="muted" style="font-size:13px;">Controller combos &amp; achievement notes</span>
        </div>
      </a>
      <a class="platform-summary-card" href="<?= View::e(Url::to('/cheats/pc')) ?>">
        <span class="platform-summary-num">PC</span>
        <div>
          <strong style="font-family:var(--f-display);font-size:20px;text-transform:uppercase;display:block;">PC Console Tracker</strong>
          <span class="muted" style="font-size:13px;">Tilde commands &amp; release watch</span>
        </div>
      </a>
    </div>
  </div>
</section>

<!-- 3. Category Quick Links -->
<section class="section" style="padding-top:16px;">
  <div class="container">
    <div class="section-header">
      <div>
        <span class="eyebrow">02 · Index</span>
        <h2>Browse by Category</h2>
        <p class="section-sub">Jump straight to weapons, invincibility, vehicles, wanted level, abilities, or weather.</p>
      </div>
    </div>

    <div class="grid-cats">
      <?php
      $iconGlyphs = [
          'crosshair' => '⊕',
          'shield'    => '◈',
          'car'       => '⚡',
          'star'      => '★',
          'bolt'      => '▲',
          'sun'       => '☀',
      ];
      foreach ($cheatCategories as $cat):
          $glyph = $iconGlyphs[$cat['icon'] ?? 'bolt'] ?? '✦';
      ?>
        <a class="paper-card cat-card" href="<?= View::e(Url::to('/cheats/category/' . $cat['slug'])) ?>">
          <div class="cat-icon" aria-hidden="true"><?= $glyph ?></div>
          <strong style="font-family:var(--f-display);font-size:18px;text-transform:uppercase;"><?= View::e($cat['name']) ?></strong>
          <span class="cat-count"><?= (int) ($cat['item_count'] ?? 0) ?> entries</span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- 4. Case Files (Latest Blog Guides) -->
<section class="section">
  <div class="container">
    <div class="section-header">
      <div>
        <span class="eyebrow">03 · Case files</span>
        <h2>Latest Guides &amp; <em class="text-accent">Intel</em></h2>
        <p class="section-sub">Verification logs, input tutorials, and honest GTA V vs GTA 6 comparisons.</p>
      </div>
      <a class="btn btn-outline btn-sm" href="<?= View::e(Url::to('/blog')) ?>">All Articles →</a>
    </div>

    <div class="grid-blog">
      <?php foreach ($latestPosts as $post): ?>
        <?php require dirname(__DIR__) . '/partials/blog-card.php'; ?>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- 5. How We Verify Trust Section -->
<section class="section">
  <div class="container">
    <div class="section-header">
      <div>
        <span class="eyebrow">04 · Evidence standard</span>
        <h2>How We Verify Codes</h2>
        <p class="section-sub">Every code is checked against retail console builds to keep fake pre-launch spam out.</p>
      </div>
      <a class="btn btn-outline btn-sm" href="<?= View::e(Url::to('/how-we-verify')) ?>">Full Methodology →</a>
    </div>

    <div class="trust-grid">
      <div class="paper-card trust-item">
        <div class="trust-num">01</div>
        <h3>Retail Console Testing</h3>
        <p class="muted" style="font-size:14px;margin:0;">Every Verified code is reproduced on unmodded PS5 and Xbox Series X|S hardware before receiving a badge.</p>
      </div>
      <div class="paper-card trust-item">
        <div class="trust-num">02</div>
        <h3>Zero Fake Tags</h3>
        <p class="muted" style="font-size:14px;margin:0;">Rumors stay marked Unconfirmed, and fake online money generators are flagged as Debunked.</p>
      </div>
      <div class="paper-card trust-item">
        <div class="trust-num">03</div>
        <h3>Trophy Safety</h3>
        <p class="muted" style="font-size:14px;margin:0;">Each dossier documents duration, side effects, and trophy or achievement lock warnings upfront.</p>
      </div>
      <div class="paper-card trust-item">
        <div class="trust-num">04</div>
        <h3>Patch Changelogs</h3>
        <p class="muted" style="font-size:14px;margin:0;">We timestamp every verification check and re-test codes whenever Rockstar ships a title update.</p>
      </div>
    </div>
  </div>
</section>

<!-- 6. Newsletter Signup -->
<section class="section">
  <div class="container">
    <?php require dirname(__DIR__) . '/partials/newsletter-box.php'; ?>
  </div>
</section>

<!-- 7. FAQ Section -->
<section class="section">
  <div class="container" style="max-width:880px;">
    <div class="section-header">
      <div>
        <span class="eyebrow">05 · Quick answers</span>
        <h2>Frequently Asked Questions</h2>
        <p class="section-sub">Direct, verified answers about GTA 6 cheat codes on PS5, Xbox Series X|S, and PC.</p>
      </div>
    </div>
    <?php $faqs = $homeFaqs; require dirname(__DIR__) . '/partials/faq-accordion.php'; ?>
  </div>
</section>
