<?php
use App\Core\Url;
use App\Core\View;
?>
<div class="adm-stats">
  <div class="adm-card">
    <div class="stat-num"><?= (int) ($statusCounts['total'] ?? 0) ?></div>
    <div class="stat-lbl">Total Cheat Entries</div>
  </div>
  <div class="adm-card">
    <div class="stat-num" style="color:var(--color-5);"><?= (int) ($statusCounts['verified'] ?? 0) ?></div>
    <div class="stat-lbl">Verified Cheats</div>
  </div>
  <div class="adm-card">
    <div class="stat-num" style="color:var(--color-4);"><?= (int) ($subsCount ?? 0) ?></div>
    <div class="stat-lbl">Confirmed Subscribers</div>
  </div>
  <div class="adm-card">
    <div class="stat-num"><?= (int) ($totalPosts ?? 0) ?></div>
    <div class="stat-lbl">Blog Guides</div>
  </div>
  <div class="adm-card">
    <div class="stat-num"><?= number_format((int) ($totalViews ?? 0)) ?></div>
    <div class="stat-lbl">Total Content Views</div>
  </div>
</div>

<?php if (!empty($retestCheats)): ?>
  <div class="adm-card" style="border-left:4px solid var(--color-2);">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;margin-bottom:12px;">
      <h2 style="margin:0;font-size:1.15rem;color:var(--color-2);">Flagged for Re-Test (Player Reports)</h2>
      <span class="badge badge-debunked"><?= count($retestCheats) ?> alert<?= count($retestCheats) === 1 ? '' : 's' ?></span>
    </div>
    <div class="adm-table-wrap">
      <table class="adm-table">
        <thead>
          <tr><th>Cheat</th><th>Patch</th><th>Working / Broken</th><th>Action</th></tr>
        </thead>
        <tbody>
          <?php foreach ($retestCheats as $rc): ?>
            <tr>
              <td><strong><?= View::e($rc['title']) ?></strong></td>
              <td><code><?= View::e($rc['game_patch'] ?? 'v1.00') ?></code></td>
              <td><?= (int) $rc['votes_working'] ?> working / <strong style="color:var(--color-2);"><?= (int) $rc['votes_broken'] ?> broken</strong></td>
              <td><a class="btn btn-primary btn-sm" href="<?= View::e(Url::to('/admin/cheats/' . (int) $rc['id'] . '/edit')) ?>">Review &amp; Re-Verify →</a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>

<div class="adm-form-grid adm-form-2col">
  <div class="adm-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
      <h2 style="margin:0;font-size:1.15rem;">Recent Admin Activity</h2>
      <span style="font-size:0.82rem;color:var(--adm-muted);"><?= (int) ($unreadContacts ?? 0) ?> new contact messages</span>
    </div>
    <div class="adm-table-wrap">
      <table class="adm-table">
        <thead>
          <tr><th>Time</th><th>User</th><th>Action</th><th>Details</th></tr>
        </thead>
        <tbody>
          <?php if (empty($recentLogs)): ?>
            <tr><td colspan="4" style="color:var(--adm-muted);">No activity logged yet.</td></tr>
          <?php else: ?>
            <?php foreach ($recentLogs as $log): ?>
              <tr>
                <td style="white-space:nowrap;color:var(--adm-muted);"><?= View::e(date('M j H:i', strtotime((string) $log['created_at']))) ?></td>
                <td><?= View::e($log['user_name'] ?? 'Admin') ?></td>
                <td><span class="badge badge-coming_soon"><?= View::e($log['action']) ?></span></td>
                <td><?= View::e($log['details'] ?? '') ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="adm-card">
    <?php
      $appCfg = require dirname(__DIR__, 2) . '/config/app.php';
      $dbCfg  = require dirname(__DIR__, 2) . '/config/database.php';
    ?>
    <h2 style="margin:0 0 14px;font-size:1.15rem;">Launch Readiness &amp; Tools</h2>
    <p style="font-size:0.9rem;color:var(--adm-muted);">
      Env: <strong><?= View::e(strtoupper((string) ($appCfg['env'] ?? 'local'))) ?></strong> ·
      Database: <strong><?= View::e((string) ($dbCfg['name'] ?? 'gta6cheatcodes')) ?></strong> ·
      Cached HTML pages: <strong><?= (int) ($cacheCount ?? 0) ?></strong><?php if (($appCfg['env'] ?? 'local') === 'local'): ?>
        (<a href="http://localhost:8888/phpMyAdmin5/" target="_blank">phpMyAdmin5 ↗</a>)
      <?php endif; ?>
    </p>
    <div style="display:grid;gap:8px;margin-top:14px;">
      <a class="btn btn-secondary" href="<?= View::e(Url::to('/admin/cheats')) ?>">Update Cheat Verification Status</a>
      <a class="btn btn-secondary" href="<?= View::e(Url::to('/admin/settings')) ?>">Settings, Mailer, IndexNow &amp; Inbox</a>
      <form action="<?= View::e(Url::to('/admin/settings/cache-clear')) ?>" method="post" style="margin:0;">
        <?= \App\Core\Security::csrfField() ?>
        <button type="submit" class="btn btn-secondary" style="width:100%;">Flush HTML Page Cache (<?= (int) ($cacheCount ?? 0) ?>)</button>
      </form>
      <a class="btn btn-secondary" href="<?= View::e(Url::to('/sitemap.xml')) ?>" target="_blank">Inspect XML Sitemap ↗</a>
      <a class="btn btn-secondary" href="<?= View::e(Url::to('/llms.txt')) ?>" target="_blank">Inspect llms.txt (AISEO) ↗</a>
    </div>
  </div>
</div>
