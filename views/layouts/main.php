<?php
use App\Core\Url;
use App\Core\View;

$seo = $seo ?? [];
$currentPath = Url::currentPath();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= View::e($seo['title'] ?? 'ViceCodes - GTA 6 Cheat Codes') ?></title>
  <meta name="description" content="<?= View::e($seo['description'] ?? '') ?>">
  <meta name="keywords" content="<?= View::e($seo['keywords'] ?? '') ?>">
  <meta name="robots" content="<?= View::e($seo['robots'] ?? 'index, follow') ?>">
  <link rel="canonical" href="<?= View::e($seo['canonical'] ?? Url::full($currentPath)) ?>">
  <link rel="alternate" type="application/rss+xml" title="<?= View::e($seo['site_name'] ?? 'ViceCodes') ?> RSS" href="<?= View::e(Url::full('/feed.xml')) ?>">

  <!-- Open Graph / Social -->
  <meta property="og:locale" content="en_US">
  <meta property="og:type" content="<?= View::e($seo['og_type'] ?? 'website') ?>">
  <meta property="og:title" content="<?= View::e($seo['title'] ?? '') ?>">
  <meta property="og:description" content="<?= View::e($seo['description'] ?? '') ?>">
  <meta property="og:url" content="<?= View::e($seo['canonical'] ?? Url::full($currentPath)) ?>">
  <meta property="og:site_name" content="<?= View::e($seo['site_name'] ?? 'ViceCodes') ?>">
  <meta property="og:image" content="<?= View::e($seo['og_image'] ?? Url::full('/assets/img/og-default.png')) ?>">
  <?php if (!empty($seo['published'])): ?>
  <meta property="article:published_time" content="<?= View::e(date('c', strtotime((string) $seo['published']))) ?>">
  <?php endif; ?>
  <?php if (!empty($seo['modified'])): ?>
  <meta property="article:modified_time" content="<?= View::e(date('c', strtotime((string) $seo['modified']))) ?>">
  <?php endif; ?>

  <!-- Twitter / X Cards -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?= View::e($seo['title'] ?? '') ?>">
  <meta name="twitter:description" content="<?= View::e($seo['description'] ?? '') ?>">
  <meta name="twitter:image" content="<?= View::e($seo['og_image'] ?? Url::full('/assets/img/og-default.png')) ?>">

  <meta name="theme-color" content="#1B1C1D">
  <link rel="manifest" href="<?= View::e(Url::to('/manifest.json')) ?>">
  <link rel="icon" type="image/svg+xml" href="<?= View::e(Url::asset('img/favicon.svg')) ?>">

  <!-- Vice Codes Design System Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Atkinson+Hyperlegible:ital,wght@0,400;0,700;1,400&family=Big+Shoulders+Display:wght@700;900&family=Kalam:wght@400;700&display=swap" rel="stylesheet">

  <!-- Critical Boot Styles -->
  <style>
    body{margin:0;background:#FAF6F0;color:#1B1C1D;font-family:'Atkinson Hyperlegible',Arial,sans-serif;}
    .site-header{position:sticky;top:0;z-index:60;background:rgba(250,246,240,.94);border-bottom:1px solid #D6CAB8;}
  </style>
  <link rel="stylesheet" href="<?= View::e(Url::asset('css/style.css')) ?>?v=2.1">

  <!-- Structured Data (JSON-LD) -->
  <?php if (!empty($seo['schemas']) && is_array($seo['schemas'])): ?>
    <?php foreach ($seo['schemas'] as $schema): ?>
      <?php if (!empty($schema)): ?>
  <script type="application/ld+json"><?= json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
      <?php endif; ?>
    <?php endforeach; ?>
  <?php endif; ?>

  <?php if (!empty($settings['ga4_id'])): ?>
  <script async src="https://www.googletagmanager.com/gtag/js?id=<?= View::e($settings['ga4_id']) ?>"></script>
  <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','<?= View::e($settings['ga4_id']) ?>');</script>
  <?php endif; ?>
  <?php if (!empty($settings['plausible_domain'])): ?>
  <script defer data-domain="<?= View::e($settings['plausible_domain']) ?>" src="https://plausible.io/js/script.js"></script>
  <?php endif; ?>
</head>
<body>
  <a class="sr-only" href="#main">Skip to content</a>

  <!-- Reading Progress Bar -->
  <div class="progress-bar" id="vc-progress" aria-hidden="true"></div>

  <!-- Ambient Topographic Paper Backdrop -->
  <div class="bd" aria-hidden="true">
    <div class="bd-glow g1"></div>
    <div class="bd-glow g2"></div>
    <div class="bd-glow g3"></div>
    <div class="bd-grid"></div>
  </div>

  <header class="site-header" id="site-header">
    <div class="container header-inner">
      <a class="brand-logo" href="<?= View::e(Url::to('/')) ?>">
        <img src="<?= View::e(Url::asset('img/favicon.svg')) ?>" alt="ViceCodes logo" width="32" height="32">
        <span>Vice<span class="text-accent">Codes</span></span>
        <span class="brand-chip">GTA 6</span>
      </a>

      <nav class="nav-links" aria-label="Main navigation">
        <a href="<?= View::e(Url::to('/')) ?>" class="<?= $currentPath === '/' ? 'active' : '' ?>">Home</a>
        <a href="<?= View::e(Url::to('/cheats')) ?>" class="<?= $currentPath === '/cheats' ? 'active' : '' ?>">Cheats</a>
        <a href="<?= View::e(Url::to('/cheats/printable')) ?>" class="<?= $currentPath === '/cheats/printable' ? 'active' : '' ?>">Printable</a>
        <a href="<?= View::e(Url::to('/cheats/ps5')) ?>" class="<?= $currentPath === '/cheats/ps5' ? 'active' : '' ?>">PS5</a>
        <a href="<?= View::e(Url::to('/cheats/xbox')) ?>" class="<?= $currentPath === '/cheats/xbox' ? 'active' : '' ?>">Xbox</a>
        <a href="<?= View::e(Url::to('/blog')) ?>" class="<?= str_starts_with($currentPath, '/blog') ? 'active' : '' ?>">Case Files</a>
        <a href="<?= View::e(Url::to('/how-we-verify')) ?>" class="<?= $currentPath === '/how-we-verify' ? 'active' : '' ?>">Verify</a>
      </nav>

      <div class="header-actions">
        <button type="button" class="cmdk-trigger" data-open-cmdk aria-label="Quick search cheats (Cmd+K)">
          <span>Quick Find</span>
          <kbd>⌘K</kbd>
        </button>
        <form class="header-search" action="<?= View::e(Url::to('/search')) ?>" method="get" role="search">
          <label for="hdr-q" class="sr-only">Search GTA 6 cheats</label>
          <input id="hdr-q" type="search" name="q" placeholder="Search cheats, cars..." value="<?= View::e($_GET['q'] ?? '') ?>">
          <button type="submit">Find</button>
        </form>
        <button id="mobile-menu-btn" class="mobile-menu-btn" type="button" aria-label="Open menu" aria-expanded="false">☰</button>
      </div>
    </div>

    <div id="mobile-drawer" class="mobile-drawer">
      <form class="mobile-search-form" action="<?= View::e(Url::to('/search')) ?>" method="get">
        <input type="search" name="q" placeholder="Search GTA 6 cheats &amp; guides...">
        <button class="btn btn-primary btn-sm" type="submit">Go</button>
      </form>
      <a href="<?= View::e(Url::to('/')) ?>">Home</a>
      <a href="<?= View::e(Url::to('/cheats')) ?>">All GTA 6 Cheats</a>
      <a href="<?= View::e(Url::to('/cheats/printable')) ?>">Printable Cheat Sheet</a>
      <a href="<?= View::e(Url::to('/cheats/ps5')) ?>">PS5 Cheat Codes</a>
      <a href="<?= View::e(Url::to('/cheats/xbox')) ?>">Xbox Series X|S Codes</a>
      <a href="<?= View::e(Url::to('/cheats/pc')) ?>">PC Cheats</a>
      <a href="<?= View::e(Url::to('/blog')) ?>">Case Files &amp; Guides</a>
      <a href="<?= View::e(Url::to('/how-we-verify')) ?>">How We Verify</a>
      <a href="<?= View::e(Url::to('/contact')) ?>">Submit a Code</a>
    </div>
  </header>

  <div class="status-banner">
    <div class="container status-banner-inner">
      <span class="status-dot" aria-hidden="true"></span>
      <strong>Live Tracker:</strong>
      <span><?= View::e($settings['launch_banner_text'] ?? '0 Verified Codes: Updated live on launch day (Nov 19, 2026)') ?></span>
      <a href="<?= View::e(Url::to('/how-we-verify')) ?>">Verification rules →</a>
    </div>
  </div>

  <?php if (!empty($flash)): ?>
    <div class="container">
      <div class="flash flash-<?= View::e($flash['type']) ?>" role="alert">
        <?= $flash['message'] ?>
      </div>
    </div>
  <?php endif; ?>

  <main id="main">
    <?= $content ?>
  </main>

  <footer class="site-footer">
    <div class="stripes" aria-hidden="true"></div>
    <div class="container">
      <div class="footer-grid">
        <div class="footer-col">
          <a class="brand-logo" href="<?= View::e(Url::to('/')) ?>" style="margin-bottom:10px;">
            <img src="<?= View::e(Url::asset('img/favicon.svg')) ?>" alt="ViceCodes logo" width="28" height="28">
            <span>Vice<span class="text-accent">Codes</span></span>
          </a>
          <p class="muted" style="font-size:14px;max-width:38ch;">
            <?= View::e($settings['site_tagline'] ?? 'Verified GTA 6 Cheat Codes for PS5, Xbox Series X|S & PC') ?>
          </p>
        </div>

        <div class="footer-col">
          <h4>Platforms</h4>
          <ul>
            <li><a href="<?= View::e(Url::to('/cheats')) ?>">All GTA 6 Cheats</a></li>
            <li><a href="<?= View::e(Url::to('/cheats/printable')) ?>">Printable Cheat Sheet</a></li>
            <li><a href="<?= View::e(Url::to('/cheats/ps5')) ?>">PS5 Cheat Codes</a></li>
            <li><a href="<?= View::e(Url::to('/cheats/xbox')) ?>">Xbox Series X|S Codes</a></li>
            <li><a href="<?= View::e(Url::to('/cheats/pc')) ?>">PC Cheat Tracker</a></li>
            <li><a href="<?= View::e(Url::to('/blog')) ?>">Case Files &amp; Guides</a></li>
          </ul>
        </div>

        <div class="footer-col">
          <h4>Categories</h4>
          <ul>
            <?php foreach (array_slice($cheatCategories ?? [], 0, 6) as $cat): ?>
              <li><a href="<?= View::e(Url::to('/cheats/category/' . $cat['slug'])) ?>"><?= View::e($cat['name']) ?></a></li>
            <?php endforeach; ?>
          </ul>
        </div>

        <div class="footer-col">
          <h4>Site</h4>
          <ul>
            <li><a href="<?= View::e(Url::to('/how-we-verify')) ?>">How We Verify Codes</a></li>
            <li><a href="<?= View::e(Url::to('/about')) ?>">About ViceCodes</a></li>
            <li><a href="<?= View::e(Url::to('/contact')) ?>">Contact / Submit Code</a></li>
            <li><a href="<?= View::e(Url::to('/disclaimer')) ?>">Disclaimer</a></li>
            <li><a href="<?= View::e(Url::to('/privacy-policy')) ?>">Privacy Policy</a></li>
            <li><a href="<?= View::e(Url::to('/terms')) ?>">Terms of Use</a></li>
          </ul>
        </div>
      </div>

      <div class="footer-disclaimer">
        <p style="margin:0 0 6px;"><strong>Disclaimer:</strong> <?= View::e($settings['footer_disclaimer'] ?? '') ?></p>
        <p style="margin:0;">&copy; <?= date('Y') ?> <?= View::e($settings['site_name'] ?? 'ViceCodes') ?>. <a href="<?= View::e(Url::to('/sitemap.xml')) ?>">Sitemap</a> · <a href="<?= View::e(Url::to('/llms.txt')) ?>">llms.txt</a> · <a href="<?= View::e(Url::to('/feed.xml')) ?>">RSS</a></p>
      </div>
    </div>
  </footer>

  <!-- Sticky Quick Cheats Loadout Bar & Drawer -->
  <div id="vc-loadout-bar" class="loadout-bar" hidden>
    <div class="loadout-bar-inner">
      <button type="button" id="vc-loadout-open" class="loadout-toggle-btn">
        <span class="loadout-star">★</span>
        <span>My Quick Cheats (<span id="vc-loadout-count">0</span>)</span>
      </button>
      <div class="loadout-bar-actions">
        <button type="button" id="vc-loadout-share" class="btn btn-sm btn-secondary">Share Loadout</button>
        <button type="button" id="vc-loadout-clear" class="btn btn-sm btn-outline" style="color:var(--color-11);border-color:rgba(246,241,231,0.35);">Clear</button>
      </div>
    </div>
  </div>

  <aside id="vc-loadout-drawer" class="loadout-drawer" aria-hidden="true">
    <div class="loadout-drawer-head">
      <div>
        <span class="eyebrow">Active Console Loadout</span>
        <h3 style="margin:2px 0 0;">My Quick Cheats</h3>
      </div>
      <button type="button" id="vc-loadout-close" class="loadout-close-btn" aria-label="Close drawer">✕</button>
    </div>
    <p class="muted" style="font-size:13px;margin:6px 0 14px;">Pinned locally in your browser &amp; cached offline for TV-side play. Max 10 cheats.</p>
    <div id="vc-loadout-items" class="loadout-items"></div>
    <div class="loadout-drawer-foot">
      <button type="button" id="vc-loadout-share-drawer" class="btn btn-primary btn-sm" style="width:100%;">Copy Shareable Loadout Link</button>
    </div>
  </aside>

  <!-- Command Palette Quick Search Modal (Cmd/Ctrl + K) -->
  <div id="vc-cmdk-modal" class="cmdk-modal" hidden aria-modal="true" role="dialog" aria-labelledby="vc-cmdk-title">
    <div class="cmdk-backdrop" data-close-cmdk></div>
    <div class="cmdk-dialog">
      <div class="cmdk-head">
        <span class="cmdk-icon" aria-hidden="true">⌕</span>
        <input id="vc-cmdk-input" type="search" placeholder="Search GTA 6 cheats, cars, phone numbers, or PS5/Xbox inputs..." autocomplete="off" aria-label="Instant cheat search">
        <button type="button" class="cmdk-esc" data-close-cmdk>ESC</button>
      </div>
      <div class="cmdk-filters" role="tablist" aria-label="Filter results by platform">
        <button type="button" class="cmdk-plat active" data-cmdk-plat="">All</button>
        <button type="button" class="cmdk-plat" data-cmdk-plat="ps5">PS5</button>
        <button type="button" class="cmdk-plat" data-cmdk-plat="xbox">Xbox</button>
        <button type="button" class="cmdk-plat" data-cmdk-plat="pc">PC</button>
        <span id="vc-cmdk-title" class="cmdk-hint">Instant Client Index</span>
      </div>
      <div id="vc-cmdk-results" class="cmdk-results">
        <div class="cmdk-empty">Type to search tracked GTA 6 cheat codes...</div>
      </div>
    </div>
  </div>

  <div id="vc-toast" class="toast" role="status" aria-live="polite"></div>
  <script>
    window.VC_APP = {
      baseUrl: <?= json_encode(Url::to('/')) ?>,
      cheatsUrl: <?= json_encode(Url::to('/cheats')) ?>,
      apiCheatsUrl: <?= json_encode(Url::to('/api/cheats.json')) ?>,
      swUrl: <?= json_encode(Url::to('/sw.js')) ?>,
      initialPlatform: <?= json_encode($activePlatform ?? '') ?>
    };
  </script>
  <script defer src="<?= View::e(Url::asset('js/app.js')) ?>?v=2.1"></script>
</body>
</html>
