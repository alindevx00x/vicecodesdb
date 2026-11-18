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

<!-- Homepage Livestream & YouTube Videos Manager -->
<?php
  $curVideoUrl = (string) ($settings['video_featured_url'] ?? 'https://www.youtube.com/watch?v=VEqImC8xFZ0');
  $curVideoId  = View::extractYoutubeId($curVideoUrl);
?>
<div class="adm-card" style="margin-top:20px;">
  <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:14px;">
    <div>
      <h2 style="margin:0;font-size:1.15rem;">Homepage Livestream &amp; YouTube Videos</h2>
      <p style="margin:4px 0 0;font-size:13px;color:var(--adm-muted);">
        Embed a YouTube livestream or video directly on the homepage (supports watch, live, shorts, and youtu.be links).
      </p>
    </div>
    <a class="btn btn-secondary btn-sm" href="<?= View::e(Url::to('/#live-watch')) ?>" target="_blank">Preview on Homepage ↗</a>
  </div>

  <form action="<?= View::e(Url::to('/admin/dashboard/videos')) ?>" method="post">
    <?= \App\Core\Security::csrfField() ?>
    <div style="display:flex;gap:18px;flex-wrap:wrap;margin-bottom:14px;">
      <label style="font-weight:700;">
        <input type="checkbox" name="video_section_enabled" value="1" <?= ($settings['video_section_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
        Show Livestream / Video Section on Homepage
      </label>
      <label style="font-weight:700;color:var(--color-2);">
        <input type="checkbox" name="video_is_live" value="1" <?= ($settings['video_is_live'] ?? '1') === '1' ? 'checked' : '' ?>>
        Mark Main Stream as LIVE NOW (Pulsing Red Badge)
      </label>
    </div>

    <div class="adm-form-grid adm-form-2col">
      <div>
        <div class="field">
          <label for="video_featured_url">Main YouTube Livestream / Video URL *</label>
          <input
            id="video_featured_url"
            type="text"
            name="video_featured_url"
            value="<?= View::e($curVideoUrl) ?>"
            placeholder="https://www.youtube.com/watch?v=VEqImC8xFZ0">
        </div>
        <div class="field">
          <label for="video_featured_title">Main Stream / Video Title</label>
          <input
            id="video_featured_title"
            type="text"
            name="video_featured_title"
            value="<?= View::e($settings['video_featured_title'] ?? 'GTA 6 Live Countdown & Launch Verification Stream') ?>">
        </div>
        <div class="field">
          <label for="video_featured_note">Main Video Caption / Notes (optional)</label>
          <input
            id="video_featured_note"
            type="text"
            name="video_featured_note"
            value="<?= View::e($settings['video_featured_note'] ?? '') ?>"
            placeholder="Streaming live ahead of the Nov 19, 2026 unlock...">
        </div>
      </div>

      <div>
        <div class="field">
          <label for="video_section_title">Homepage Section Heading</label>
          <input
            id="video_section_title"
            type="text"
            name="video_section_title"
            value="<?= View::e($settings['video_section_title'] ?? 'GTA 6 Live Stream & Watch Desk') ?>">
        </div>
        <div class="field">
          <label for="video_section_subtitle">Homepage Section Subtitle</label>
          <input
            id="video_section_subtitle"
            type="text"
            name="video_section_subtitle"
            value="<?= View::e($settings['video_section_subtitle'] ?? '') ?>">
        </div>
        <div id="vc-dash-yt-preview" style="padding:10px;border:1px solid var(--adm-border);border-radius:8px;background:var(--color-13);display:flex;align-items:center;gap:12px;">
          <?php if ($curVideoId): ?>
            <img id="vc-dash-yt-thumb" src="https://i.ytimg.com/vi/<?= View::e($curVideoId) ?>/hqdefault.jpg" alt="YouTube preview" style="width:120px;height:68px;object-fit:cover;border-radius:6px;flex-shrink:0;">
            <div style="font-size:12px;">
              <strong style="display:block;color:var(--color-1);">Detected YouTube ID: <code id="vc-dash-yt-id"><?= View::e($curVideoId) ?></code></strong>
              <span style="color:var(--adm-muted);">Embed ready via youtube-nocookie.com</span>
            </div>
          <?php else: ?>
            <img id="vc-dash-yt-thumb" src="" alt="" hidden style="width:120px;height:68px;object-fit:cover;border-radius:6px;flex-shrink:0;">
            <div style="font-size:12px;color:var(--adm-muted);">Paste a YouTube link to preview thumbnail (<code id="vc-dash-yt-id">none</code>).</div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <hr style="border-color:var(--adm-border);margin:16px 0;">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;margin-bottom:10px;">
      <h3 style="margin:0;font-size:1rem;">Additional YouTube Videos / Clips (Optional Playlist Strip)</h3>
      <button type="button" id="add-video-row" class="btn btn-secondary btn-sm">+ Add YouTube Video</button>
    </div>

    <div id="video-rows" style="display:grid;gap:8px;margin-bottom:14px;">
      <?php foreach ($extraVideos as $idx => $ev): ?>
        <div class="video-admin-row" style="display:grid;grid-template-columns:1fr 1.3fr auto auto;gap:8px;align-items:center;">
          <input type="text" name="extra_title[<?= (int) $idx ?>]" value="<?= View::e($ev['title'] ?? '') ?>" placeholder="Video title">
          <input type="text" name="extra_url[<?= (int) $idx ?>]" value="<?= View::e($ev['url'] ?? '') ?>" placeholder="https://www.youtube.com/watch?v=...">
          <label style="font-size:12px;white-space:nowrap;">
            <input type="checkbox" name="extra_live[<?= (int) $idx ?>]" value="1" <?= !empty($ev['is_live']) ? 'checked' : '' ?>> Live
          </label>
          <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.remove()">✕</button>
        </div>
      <?php endforeach; ?>
    </div>

    <button type="submit" class="btn btn-primary">Save Livestream &amp; Videos</button>
  </form>
</div>
