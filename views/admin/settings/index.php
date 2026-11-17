<?php
use App\Core\Security;
use App\Core\Url;
use App\Core\View;
?>
<div class="adm-form-grid adm-form-2col">
  <div class="adm-card">
    <h2 style="margin-top:0;font-size:1.15rem;">Site, Performance, Mailer &amp; Security Configuration</h2>
    <form action="<?= View::e(Url::to('/admin/settings/save')) ?>" method="post">
      <?= Security::csrfField() ?>
      <div class="field">
        <label for="site_name">Site Name</label>
        <input id="site_name" type="text" name="site_name" value="<?= View::e($settings['site_name'] ?? '') ?>">
      </div>
      <div class="field">
        <label for="site_tagline">Site Tagline</label>
        <input id="site_tagline" type="text" name="site_tagline" value="<?= View::e($settings['site_tagline'] ?? '') ?>">
      </div>
      <div class="field">
        <label for="launch_mode">Launch Mode</label>
        <select id="launch_mode" name="launch_mode">
          <option value="pre_launch" <?= ($settings['launch_mode'] ?? '') === 'pre_launch' ? 'selected' : '' ?>>Pre-Launch Countdown (Before Nov 19, 2026)</option>
          <option value="launched" <?= ($settings['launch_mode'] ?? '') === 'launched' ? 'selected' : '' ?>>Launched (Codes Live Mode)</option>
        </select>
      </div>
      <div class="field">
        <label for="launch_date">Launch Date (ISO 8601 UTC)</label>
        <input id="launch_date" type="text" name="launch_date" value="<?= View::e($settings['launch_date'] ?? '2026-11-19T00:00:00Z') ?>">
      </div>
      <div class="field">
        <label for="launch_banner_text">Top Live Ticker Banner Text</label>
        <input id="launch_banner_text" type="text" name="launch_banner_text" value="<?= View::e($settings['launch_banner_text'] ?? '') ?>">
      </div>
      <div class="field">
        <label for="default_meta_title">Default Homepage Meta Title</label>
        <input id="default_meta_title" type="text" name="default_meta_title" value="<?= View::e($settings['default_meta_title'] ?? '') ?>">
      </div>
      <div class="field">
        <label for="default_meta_description">Default Meta Description</label>
        <textarea id="default_meta_description" name="default_meta_description" rows="2"><?= View::e($settings['default_meta_description'] ?? '') ?></textarea>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <div class="field">
          <label for="ga4_id">Google Analytics 4 ID (G-...)</label>
          <input id="ga4_id" type="text" name="ga4_id" value="<?= View::e($settings['ga4_id'] ?? '') ?>">
        </div>
        <div class="field">
          <label for="plausible_domain">Plausible Analytics Domain</label>
          <input id="plausible_domain" type="text" name="plausible_domain" value="<?= View::e($settings['plausible_domain'] ?? '') ?>">
        </div>
      </div>

      <hr style="border-color:var(--adm-border);margin:18px 0;">
      <h3 style="margin:0 0 10px;">Page Cache &amp; IndexNow SEO</h3>
      <div class="field">
        <label><input type="checkbox" name="page_cache_enabled" value="1" <?= ($settings['page_cache_enabled'] ?? '1') === '1' ? 'checked' : '' ?>> Enable File-Based Full Page HTML Cache (<?= (int) ($cacheCount ?? 0) ?> cached files)</label>
      </div>
      <div class="field">
        <label><input type="checkbox" name="indexnow_enabled" value="1" <?= ($settings['indexnow_enabled'] ?? '1') === '1' ? 'checked' : '' ?>> Auto-Ping IndexNow (Bing / Yandex / Copilot) when cheats verify or articles publish</label>
      </div>
      <div class="field">
        <label for="indexnow_key">IndexNow Verification Key (served at <code>/{key}.txt</code>)</label>
        <input id="indexnow_key" type="text" name="indexnow_key" value="<?= View::e($settings['indexnow_key'] ?? '') ?>">
      </div>

      <hr style="border-color:var(--adm-border);margin:18px 0;">
      <h3 style="margin:0 0 10px;">Transactional Email Delivery (Mailer)</h3>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <div class="field">
          <label for="mail_driver">Mail Driver</label>
          <select id="mail_driver" name="mail_driver">
            <?php foreach (['log' => 'Local Log Only (Dev)', 'resend' => 'Resend API', 'brevo' => 'Brevo (Sendinblue) API', 'smtp' => 'Custom SMTP', 'mail' => 'PHP mail()'] as $mk => $ml): ?>
              <option value="<?= $mk ?>" <?= ($settings['mail_driver'] ?? 'log') === $mk ? 'selected' : '' ?>><?= $ml ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field">
          <label for="mail_from">From Email Address</label>
          <input id="mail_from" type="email" name="mail_from" value="<?= View::e($settings['mail_from'] ?? '') ?>" placeholder="hello@vicecodes.com">
        </div>
      </div>
      <div class="field">
        <label for="mail_api_key">Resend / Brevo API Key (when using API driver)</label>
        <input id="mail_api_key" type="password" name="mail_api_key" value="<?= View::e($settings['mail_api_key'] ?? '') ?>" autocomplete="off">
      </div>
      <div style="display:grid;grid-template-columns:2fr 1fr 1fr;gap:10px;">
        <div class="field">
          <label for="smtp_host">SMTP Host</label>
          <input id="smtp_host" type="text" name="smtp_host" value="<?= View::e($settings['smtp_host'] ?? '') ?>" placeholder="smtp.mailgun.org">
        </div>
        <div class="field">
          <label for="smtp_port">SMTP Port</label>
          <input id="smtp_port" type="text" name="smtp_port" value="<?= View::e($settings['smtp_port'] ?? '587') ?>">
        </div>
        <div class="field">
          <label for="smtp_encryption">Encryption</label>
          <select id="smtp_encryption" name="smtp_encryption">
            <option value="tls" <?= ($settings['smtp_encryption'] ?? 'tls') === 'tls' ? 'selected' : '' ?>>TLS</option>
            <option value="ssl" <?= ($settings['smtp_encryption'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL</option>
            <option value="none" <?= ($settings['smtp_encryption'] ?? '') === 'none' ? 'selected' : '' ?>>None</option>
          </select>
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <div class="field">
          <label for="smtp_user">SMTP Username</label>
          <input id="smtp_user" type="text" name="smtp_user" value="<?= View::e($settings['smtp_user'] ?? '') ?>">
        </div>
        <div class="field">
          <label for="smtp_pass">SMTP Password</label>
          <input id="smtp_pass" type="password" name="smtp_pass" value="<?= View::e($settings['smtp_pass'] ?? '') ?>" autocomplete="off">
        </div>
      </div>

      <hr style="border-color:var(--adm-border);margin:18px 0;">
      <h3 style="margin:0 0 10px;">Admin Security (Optional TOTP 2FA &amp; IP Allowlist)</h3>
      <div class="field">
        <label><input type="checkbox" name="admin_2fa_enabled" value="1" <?= ($settings['admin_2fa_enabled'] ?? '0') === '1' ? 'checked' : '' ?>> Require 6-digit TOTP Authenticator Code on Admin Login</label>
      </div>
      <div class="field">
        <label for="admin_2fa_secret">TOTP Base32 Secret (add to 1Password / Google Authenticator before enabling)</label>
        <input id="admin_2fa_secret" type="text" name="admin_2fa_secret" value="<?= View::e(($settings['admin_2fa_secret'] ?? '') ?: ($settings['admin_2fa_secret_preview'] ?? '')) ?>">
      </div>
      <div class="field">
        <label for="admin_ip_allowlist">Admin IP Allowlist (comma-separated IPs or CIDRs, leave empty to allow all)</label>
        <input id="admin_ip_allowlist" type="text" name="admin_ip_allowlist" value="<?= View::e($settings['admin_ip_allowlist'] ?? '') ?>" placeholder="e.g. 127.0.0.1, ::1">
      </div>

      <hr style="border-color:var(--adm-border);margin:18px 0;">
      <div class="field">
        <label for="footer_disclaimer">Footer Disclaimer (Mandatory Legal Notice)</label>
        <textarea id="footer_disclaimer" name="footer_disclaimer" rows="2"><?= View::e($settings['footer_disclaimer'] ?? '') ?></textarea>
      </div>
      <div class="field">
        <label><input type="checkbox" name="maintenance_mode" value="1" <?= ($settings['maintenance_mode'] ?? '0') === '1' ? 'checked' : '' ?>> Enable Maintenance Mode (Public sees 503 screen)</label>
      </div>
      <button type="submit" class="btn btn-primary">Save Settings</button>
    </form>

    <form action="<?= View::e(Url::to('/admin/settings/cache-clear')) ?>" method="post" style="margin-top:12px;">
      <?= Security::csrfField() ?>
      <button type="submit" class="btn btn-secondary">Flush HTML Page Cache Now (<?= (int) ($cacheCount ?? 0) ?> files)</button>
    </form>
  </div>

  <div class="adm-card">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:12px;">
      <h2 style="margin:0;font-size:1.15rem;">Contact &amp; Code Submissions Inbox</h2>
      <div style="display:flex;gap:6px;flex-wrap:wrap;font-size:12px;">
        <a class="btn btn-sm <?= empty($inboxFilter) ? 'btn-primary' : 'btn-secondary' ?>" href="<?= View::e(Url::to('/admin/settings')) ?>">Active</a>
        <a class="btn btn-sm <?= ($inboxFilter ?? '') === 'new' ? 'btn-primary' : 'btn-secondary' ?>" href="<?= View::e(Url::to('/admin/settings?inbox=new')) ?>">New</a>
        <a class="btn btn-sm <?= ($inboxFilter ?? '') === 'read' ? 'btn-primary' : 'btn-secondary' ?>" href="<?= View::e(Url::to('/admin/settings?inbox=read')) ?>">Read</a>
        <a class="btn btn-sm <?= ($inboxFilter ?? '') === 'archived' ? 'btn-primary' : 'btn-secondary' ?>" href="<?= View::e(Url::to('/admin/settings?inbox=archived')) ?>">Archived</a>
      </div>
    </div>

    <?php if (empty($messages)): ?>
      <p style="color:var(--adm-muted);">No contact messages in this view.</p>
    <?php else: ?>
      <div style="display:grid;gap:10px;">
        <?php foreach ($messages as $m):
          $mStatus = $m['status'] ?? 'new';
        ?>
          <div style="padding:12px;border:1px solid var(--color-6);border-radius:6px;background:var(--color-13);">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;font-size:13px;">
              <strong><?= View::e($m['subject']) ?></strong>
              <span class="badge badge-<?= View::e($mStatus) ?>"><?= View::e($mStatus) ?></span>
            </div>
            <div style="font-size:12px;color:var(--color-3);margin-top:2px;">
              <?= View::e($m['name']) ?> &lt;<?= View::e($m['email']) ?>&gt; · <?= View::e(date('M j, H:i', strtotime((string) $m['created_at']))) ?>
            </div>
            <p style="margin:8px 0 10px;font-size:14px;color:var(--color-1);"><?= nl2br(View::e($m['message'])) ?></p>
            <div class="inbox-actions">
              <?php if ($mStatus !== 'read'): ?>
                <form action="<?= View::e(Url::to('/admin/contact/' . (int) $m['id'] . '/status')) ?>" method="post" style="display:inline;">
                  <?= Security::csrfField() ?>
                  <input type="hidden" name="status" value="read">
                  <button type="submit" class="btn btn-secondary btn-sm">Mark Read</button>
                </form>
              <?php endif; ?>
              <?php if ($mStatus !== 'archived'): ?>
                <form action="<?= View::e(Url::to('/admin/contact/' . (int) $m['id'] . '/status')) ?>" method="post" style="display:inline;">
                  <?= Security::csrfField() ?>
                  <input type="hidden" name="status" value="archived">
                  <button type="submit" class="btn btn-secondary btn-sm">Archive</button>
                </form>
              <?php endif; ?>
              <form action="<?= View::e(Url::to('/admin/contact/' . (int) $m['id'] . '/to-cheat')) ?>" method="post" style="display:inline;">
                <?= Security::csrfField() ?>
                <button type="submit" class="btn btn-primary btn-sm">Convert to Draft Cheat →</button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>
