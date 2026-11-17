<?php
use App\Core\Security;
use App\Core\Url;
use App\Core\View;

$badge = View::statusBadge($cheat['status'] ?? 'coming_soon');
$platforms = $cheat['platforms'] ?? [];
$patchVer = trim((string) ($cheat['game_patch'] ?? 'v1.00')) ?: 'v1.00';

$firstSeq = '';
$phoneNumbers = [];
foreach ($platforms as $pKey => $pRow) {
    if ($firstSeq === '' && !empty($pRow['code_sequence'])) {
        $firstSeq = $pRow['code_sequence'];
    }
    if (!empty($pRow['cell_number'])) {
        $phoneNumbers[$pKey] = $pRow['cell_number'];
    }
}
$primaryPhone = reset($phoneNumbers) ?: '';

$votesWorking = (int) ($cheat['votes_working'] ?? 0);
$votesBroken = (int) ($cheat['votes_broken'] ?? 0);
$totalVotes = $votesWorking + $votesBroken;
$workingPct = $totalVotes > 0 ? (int) round(($votesWorking / $totalVotes) * 100) : 100;

$loadoutPayload = [
    'slug'  => $cheat['slug'],
    'title' => $cheat['title'],
    'url'   => Url::to('/cheats/' . $cheat['slug']),
    'patch' => $patchVer,
    'ps5'   => $platforms['ps5']['code_sequence'] ?? '',
    'xbox'  => $platforms['xbox']['code_sequence'] ?? '',
    'pc'    => $platforms['pc']['code_sequence'] ?? '',
    'phone' => $primaryPhone,
];
?>
<section class="section" style="padding-top:32px;padding-bottom:88px;">
  <div class="container" style="max-width:960px;">
    <?php require dirname(__DIR__, 2) . '/partials/breadcrumbs.php'; ?>

    <div class="paper-card" style="padding:26px;margin-bottom:24px;">
      <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;margin-bottom:10px;">
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
          <?php if (!empty($cheat['category_name'])): ?>
            <a class="cheat-cat-tag" href="<?= View::e(Url::to('/cheats/category/' . $cheat['category_slug'])) ?>">
              <?= View::e($cheat['category_name']) ?>
            </a>
          <?php endif; ?>
          <span class="patch-chip">
            <?= ($cheat['status'] ?? '') === 'verified' ? 'Verified on Patch ' : 'Tracked for Patch ' ?><strong><?= View::e($patchVer) ?></strong>
          </span>
        </div>
        <div style="display:flex;align-items:center;gap:8px;">
          <button
            type="button"
            class="btn btn-secondary btn-sm fav-pin-text-btn"
            data-loadout-toggle="<?= View::e(json_encode($loadoutPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?>"
            aria-pressed="false">
            ★ Save to Quick Cheats
          </button>
          <span class="badge <?= View::e($badge['class']) ?>"><?= View::e($badge['icon'] . ' ' . $badge['label']) ?></span>
        </div>
      </div>

      <h1 style="margin-bottom:10px;"><?= View::e($cheat['title']) ?></h1>
      <p class="muted" style="font-size:17px;margin:0 0 18px;"><?= View::e($cheat['short_description']) ?></p>

      <div style="display:flex;flex-wrap:wrap;gap:18px;font-size:13px;color:var(--text-secondary);border-top:1px solid var(--border-color);padding-top:12px;">
        <span><strong>Last Checked:</strong> <?= View::e(date('F j, Y', strtotime((string) ($cheat['last_verified_at'] ?? $cheat['updated_at'])))) ?></span>
        <?php if (!empty($cheat['effects'])): ?>
          <span><strong>Effect:</strong> <?= View::e($cheat['effects']) ?></span>
        <?php endif; ?>
      </div>
    </div>

    <!-- Platform Codes Cards -->
    <div class="paper-card" style="padding:24px;margin-bottom:24px;">
      <h2>Controller &amp; Phone <em class="text-accent">Inputs</em></h2>
      <div style="display:grid;gap:14px;margin-top:14px;">
        <?php foreach (['ps5' => 'PlayStation 5 (DualSense)', 'xbox' => 'Xbox Series X|S', 'pc' => 'PC'] as $pKey => $pLabel): ?>
          <?php $row = $platforms[$pKey] ?? null; if (!$row) continue; ?>
          <div style="padding:16px;border-radius:8px;background:var(--bg-page);border:1px solid var(--border-color);">
            <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:10px;margin-bottom:10px;">
              <strong style="color:var(--color-2);font-family:var(--f-display);letter-spacing:0.04em;font-size:20px;text-transform:uppercase;"><?= View::e($pLabel) ?></strong>
              <button type="button" class="copy-btn" data-copy-text="<?= View::e($row['code_sequence']) ?>">Copy Code</button>
            </div>
            <div><?= View::formatButtonSequence((string) $row['code_sequence'], $pKey) ?></div>
            <?php if (!empty($row['cell_number'])): ?>
              <div style="margin-top:8px;font-size:14px;">Phone Dial: <strong style="color:var(--color-2);"><?= View::e($row['cell_number']) ?></strong></div>
            <?php endif; ?>
            <?php if (!empty($row['notes'])): ?>
              <div class="muted" style="margin-top:6px;font-size:13px;"><?= View::e($row['notes']) ?></div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Per-Cheat Cross-Platform Comparison Table (High-Signal for Players & AI Search) -->
    <?php if (!empty($platforms)): ?>
      <div class="paper-card" style="padding:24px;margin-bottom:24px;">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:12px;">
          <h2 style="margin:0;">Platform Comparison <em class="text-accent">Table</em></h2>
          <a class="btn btn-outline btn-sm" href="<?= View::e(Url::to('/cheats/printable')) ?>">All Codes Printable Sheet →</a>
        </div>
        <div class="adm-table-wrap" style="overflow-x:auto;">
          <table class="printable-table">
            <thead>
              <tr>
                <th>Platform</th>
                <th>Controller / Console Input</th>
                <th>Phone Number</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach (['ps5' => 'PS5 DualSense', 'xbox' => 'Xbox Series X|S', 'pc' => 'PC'] as $pKey => $pLabel):
                $row = $platforms[$pKey] ?? null;
                if (!$row) continue;
              ?>
                <tr>
                  <td><strong><?= View::e($pLabel) ?></strong></td>
                  <td><?= View::formatButtonSequence((string) $row['code_sequence'], $pKey) ?></td>
                  <td><code class="code-mono"><?= View::e($row['cell_number'] ?: '-') ?></code></td>
                  <td><span class="badge <?= View::e($badge['class']) ?>"><?= View::e($badge['label']) ?></span></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endif; ?>

    <!-- Interactive Vice City Phone Dialer Preview (Shown for Smartphone Dial Cheats) -->
    <?php if ($primaryPhone !== ''): ?>
      <div class="paper-card dialer-card" style="padding:24px;margin-bottom:24px;" data-phone-dialer="<?= View::e($primaryPhone) ?>">
        <div class="dialer-grid">
          <div>
            <span class="eyebrow">In-Game Smartphone Mode</span>
            <h2 style="margin:4px 0 8px;">Interactive <em class="text-accent">Phone Dialer</em></h2>
            <p class="muted" style="margin:0 0 14px;">Preview the exact keypad tap cadence and audio tones before dialing <strong><?= View::e($primaryPhone) ?></strong> on your character's phone in Leonida.</p>
            <div class="dialer-readout" id="vc-dialer-screen"><?= View::e($primaryPhone) ?></div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:12px;">
              <button type="button" class="btn btn-primary btn-sm" id="vc-dialer-play">Play Dial Pattern</button>
              <button type="button" class="btn btn-secondary btn-sm" data-copy-text="<?= View::e($primaryPhone) ?>">Copy Number</button>
            </div>
          </div>
          <div class="phone-mockup" aria-label="Vice City smartphone keypad preview">
            <div class="phone-speaker"></div>
            <div class="phone-keypad">
              <?php foreach (['1', '2', '3', '4', '5', '6', '7', '8', '9', '*', '0', '#'] as $digit): ?>
                <button type="button" class="phone-key" data-dial-digit="<?= View::e($digit) ?>"><?= View::e($digit) ?></button>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <!-- Community "Still Working?" Feedback Widget -->
    <div class="paper-card feedback-widget" style="padding:22px 24px;margin-bottom:24px;"
         data-feedback-url="<?= View::e(Url::to('/cheats/' . (int) $cheat['id'] . '/feedback')) ?>"
         data-cheat-id="<?= (int) $cheat['id'] ?>"
         data-csrf="<?= View::e(Security::csrfToken()) ?>">
      <div class="feedback-row">
        <div>
          <span class="eyebrow">Live Community Verification</span>
          <h3 style="margin:2px 0 4px;">Still Working on Patch <?= View::e($patchVer) ?>?</h3>
          <p class="muted" id="vc-feedback-summary" style="margin:0;font-size:14px;">
            <?php if ($totalVotes > 0): ?>
              <strong><?= $workingPct ?>%</strong> of <strong><?= $totalVotes ?></strong> player<?= $totalVotes === 1 ? '' : 's' ?> confirmed working
              (<?= $votesWorking ?> working · <?= $votesBroken ?> reported issue)
            <?php else: ?>
              Be the first player to confirm whether this cheat works on Patch <?= View::e($patchVer) ?>.
            <?php endif; ?>
          </p>
          <div class="feedback-meter" aria-hidden="true">
            <div class="feedback-meter-fill" id="vc-feedback-bar" style="width:<?= $workingPct ?>%;"></div>
          </div>
        </div>
        <div class="feedback-actions">
          <button type="button" class="btn btn-secondary btn-sm js-vote-btn" data-vote="working">👍 Still Working (<span id="vc-votes-working"><?= $votesWorking ?></span>)</button>
          <button type="button" class="btn btn-outline btn-sm js-vote-btn" data-vote="broken">⚠ Report Broken (<span id="vc-votes-broken"><?= $votesBroken ?></span>)</button>
        </div>
      </div>
      <?php if (!empty($cheat['needs_retest'])): ?>
        <div class="flash flash-error" style="margin:12px 0 0;padding:8px 12px;">
          Heads up: Recent player reports flagged this code for editor re-testing after a recent patch.
        </div>
      <?php endif; ?>
    </div>

    <!-- Verification Notes -->
    <?php if (!empty($cheat['verification_notes'])): ?>
      <div class="quick-answer-box">
        <div class="quick-answer-label">Editorial Verification Notes</div>
        <p style="margin:0;"><?= View::e($cheat['verification_notes']) ?></p>
      </div>
    <?php endif; ?>

    <!-- Rich TinyMCE Content (Auto-linked internally) -->
    <?php if (!empty($cheat['content'])): ?>
      <article class="paper-card prose" style="padding:26px;margin-bottom:24px;max-width:none;">
        <?= $cheat['content'] ?>
      </article>
    <?php endif; ?>

    <!-- Tags Cluster Links -->
    <?php if (!empty($tags)): ?>
      <div style="display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin-bottom:24px;">
        <span class="eyebrow" style="margin-right:4px;">Topics:</span>
        <?php foreach ($tags as $t): ?>
          <a class="tag-pill" href="<?= View::e(Url::to('/tag/' . $t['slug'])) ?>">#<?= View::e($t['name']) ?></a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- Changelog -->
    <?php if (!empty($changelog)): ?>
      <div class="paper-card" style="padding:22px;margin-bottom:24px;">
        <h3>Verification Changelog</h3>
        <ul style="margin:10px 0 0;padding-left:18px;color:var(--text-secondary);">
          <?php foreach ($changelog as $entry): ?>
            <li><strong><?= View::e($entry['date'] ?? '') ?>:</strong> <?= View::e($entry['note'] ?? '') ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <!-- Per-Cheat FAQs -->
    <?php if (!empty($faqs)): ?>
      <div style="margin-bottom:32px;">
        <h2>Cheat Code <em class="text-accent">FAQ</em></h2>
        <?php require dirname(__DIR__, 2) . '/partials/faq-accordion.php'; ?>
      </div>
    <?php endif; ?>

    <!-- Related Guides & Tutorials (Internal Topical Cluster) -->
    <?php if (!empty($relatedGuides)): ?>
      <div style="margin-bottom:34px;">
        <div class="section-header" style="margin-bottom:14px;">
          <h2 style="margin:0;">Related <em class="text-accent">Guides</em></h2>
          <a class="btn btn-outline btn-sm" href="<?= View::e(Url::to('/blog')) ?>">All Case Files →</a>
        </div>
        <div class="grid-blog">
          <?php foreach ($relatedGuides as $post): ?>
            <?php require dirname(__DIR__, 2) . '/partials/blog-card.php'; ?>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

    <!-- Related Cheats -->
    <?php if (!empty($relatedCheats)): ?>
      <div>
        <h2>Related GTA 6 <em class="text-accent">Cheats</em></h2>
        <div class="grid-cheats" style="margin-top:16px;">
          <?php foreach (array_slice($relatedCheats, 0, 3) as $cheat): ?>
            <?php require dirname(__DIR__, 2) . '/partials/cheat-card.php'; ?>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- Sticky Mobile Bottom Action Bar -->
<?php if ($firstSeq !== ''): ?>
  <div class="mobile-sticky-bar">
    <div style="font-size:13px;">
      <strong style="display:block;">Copy Active Code</strong>
      <span class="muted"><?= View::e(mb_strimwidth($firstSeq, 0, 34, '...')) ?></span>
    </div>
    <button type="button" class="btn btn-primary btn-sm" data-copy-text="<?= View::e($firstSeq) ?>">Copy Code</button>
  </div>
<?php endif; ?>
