<?php
use App\Core\Url;
use App\Core\View;
?>
<section class="section" style="padding-top:32px;">
  <div class="container">
    <div class="no-print">
      <?php require dirname(__DIR__, 2) . '/partials/breadcrumbs.php'; ?>
    </div>

    <div class="section-header" style="margin-bottom:18px;">
      <div>
        <span class="eyebrow">Single-Page Console Reference</span>
        <h1>GTA 6 All Cheat Codes <em class="text-accent">Printable Sheet</em></h1>
        <p class="section-sub">Clean, high-contrast comparison table listing Effect, PS5 DualSense sequence, Xbox Series X|S sequence, Smartphone dial number, and verification status.</p>
      </div>
      <div class="no-print" style="display:flex;gap:10px;flex-wrap:wrap;">
        <button type="button" class="btn btn-primary" onclick="window.print()">Print / Save PDF</button>
        <a class="btn btn-secondary" href="<?= View::e(Url::to('/cheats')) ?>">Card View</a>
      </div>
    </div>

    <div class="paper-card printable-wrap">
      <table class="printable-table">
        <thead>
          <tr>
            <th>Cheat &amp; Effect</th>
            <th>PS5 Sequence</th>
            <th>Xbox Series X|S</th>
            <th>Phone Number</th>
            <th>Status &amp; Patch</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($cheats as $c):
            $badge = View::statusBadge($c['status'] ?? 'coming_soon');
            $plats = $c['platforms'] ?? [];
            $ps5Seq = $plats['ps5']['code_sequence'] ?? 'Coming at launch';
            $xboxSeq = $plats['xbox']['code_sequence'] ?? 'Coming at launch';
            $phone = $plats['ps5']['cell_number'] ?? ($plats['xbox']['cell_number'] ?? ($plats['pc']['cell_number'] ?? '-'));
            $patch = trim((string) ($c['game_patch'] ?? 'v1.00')) ?: 'v1.00';
          ?>
            <tr>
              <td>
                <a href="<?= View::e(Url::to('/cheats/' . $c['slug'])) ?>" style="font-weight:700;color:var(--color-1);text-decoration:none;display:block;font-size:15px;">
                  <?= View::e($c['title']) ?>
                </a>
                <span style="font-size:13px;color:var(--text-secondary);"><?= View::e($c['effects'] ?: $c['short_description']) ?></span>
              </td>
              <td><?= View::formatButtonSequence((string) $ps5Seq, 'ps5') ?></td>
              <td><?= View::formatButtonSequence((string) $xboxSeq, 'xbox') ?></td>
              <td><code class="code-mono"><?= View::e($phone ?: '-') ?></code></td>
              <td style="white-space:nowrap;">
                <span class="badge <?= View::e($badge['class']) ?>"><?= View::e($badge['label']) ?></span>
                <div style="font-size:11px;font-family:var(--f-mono);color:var(--text-secondary);margin-top:4px;">Patch <?= View::e($patch) ?></div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>
