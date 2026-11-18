<?php
use App\Core\Security;
use App\Core\Url;
use App\Core\View;
?>
<div class="adm-form-grid adm-form-2col" style="margin-bottom:20px;">
  <div class="adm-card">
    <h2 style="margin-top:0;font-size:1.15rem;">Add New Trust Badge Code</h2>
    <p style="font-size:13px;color:var(--adm-muted);margin-top:0;">
      Paste the badge HTML snippet (such as an <code>&lt;a&gt;&lt;img /&gt;&lt;/a&gt;</code> embed) to show it inside the Recognition &amp; Trust footer carousel across every page.
    </p>
    <form action="<?= View::e(Url::to('/admin/badges/store')) ?>" method="post">
      <?= Security::csrfField() ?>
      <div class="field">
        <label for="badge_label">Badge Name / Label (optional — auto-detected from alt/URL)</label>
        <input id="badge_label" type="text" name="label" placeholder="e.g. Featured on Twelve Tools">
      </div>
      <div class="field">
        <label for="badge_html">Badge HTML Code *</label>
        <textarea
          id="badge_html"
          name="badge_html"
          rows="4"
          required
          style="font-family:monospace;font-size:12px;"
          placeholder='<a href="https://twelve.tools" target="_blank">&#10;  <img src="https://twelve.tools/badge1-white.svg" alt="Featured on Twelve Tools" width="200" height="54" />&#10;</a>'></textarea>
      </div>
      <div style="display:grid;grid-template-columns:120px 1fr;gap:12px;align-items:center;">
        <div class="field">
          <label for="badge_sort">Sort Order</label>
          <input id="badge_sort" type="number" name="sort_order" value="<?= count($badges) + 1 ?>">
        </div>
        <div class="field" style="margin-top:16px;">
          <label><input type="checkbox" name="is_active" value="1" checked> Active in Footer Carousel</label>
        </div>
      </div>
      <button type="submit" class="btn btn-primary">+ Add Badge Code</button>
    </form>
  </div>

  <div class="adm-card">
    <h2 style="margin-top:0;font-size:1.15rem;">Footer Carousel Display Settings</h2>
    <form action="<?= View::e(Url::to('/admin/badges/settings')) ?>" method="post">
      <?= Security::csrfField() ?>
      <div class="field">
        <label><input type="checkbox" name="badges_enabled" value="1" <?= ($settings['badges_enabled'] ?? '1') === '1' ? 'checked' : '' ?>> Show Recognition &amp; Trust Carousel in Footer of All Pages</label>
      </div>
      <div class="field">
        <label for="badges_heading">Section Title</label>
        <input id="badges_heading" type="text" name="badges_heading" value="<?= View::e($settings['badges_heading'] ?? 'Recognition & Trust') ?>">
      </div>
      <div class="field">
        <label for="badges_subheading">Subtitle</label>
        <input id="badges_subheading" type="text" name="badges_subheading" value="<?= View::e($settings['badges_subheading'] ?? 'Our product has gained industry recognition') ?>">
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <div class="field">
          <label for="badges_speed">Scroll Speed (px/sec)</label>
          <input id="badges_speed" type="number" min="20" max="400" name="badges_speed" value="<?= (int) ($settings['badges_speed'] ?? 100) ?>">
        </div>
        <div class="field">
          <label for="badges_bg_style">Carousel Backdrop</label>
          <select id="badges_bg_style" name="badges_bg_style">
            <option value="dark" <?= ($settings['badges_bg_style'] ?? 'dark') === 'dark' ? 'selected' : '' ?>>Dark Slab (#1B1C1D — Best for white SVGs)</option>
            <option value="light" <?= ($settings['badges_bg_style'] ?? 'dark') === 'light' ? 'selected' : '' ?>>Paper Surface (Matches Footer)</option>
          </select>
        </div>
      </div>
      <button type="submit" class="btn btn-secondary">Save Carousel Settings</button>
    </form>
  </div>
</div>

<div class="adm-card">
  <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:14px;">
    <h2 style="margin:0;font-size:1.15rem;">Managed Badge Codes (<?= count($badges) ?>)</h2>
    <span style="font-size:12px;color:var(--adm-muted);">Edit any badge HTML code inline in the Codes column and click Save.</span>
  </div>

  <?php if (empty($badges)): ?>
    <p style="color:var(--adm-muted);">No badges added yet. Paste your first badge embed code above.</p>
  <?php else: ?>
    <div class="adm-table-wrap">
      <table class="adm-table">
        <thead>
          <tr>
            <th>Preview</th>
            <th>Label &amp; Order</th>
            <th>Badge HTML Code</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($badges as $b):
            $formId = 'badge-form-' . (int) $b['id'];
            $isDark = ($settings['badges_bg_style'] ?? 'dark') === 'dark';
          ?>
            <tr>
              <td style="min-width:190px;">
                <div style="display:inline-flex;align-items:center;justify-content:center;padding:10px 14px;border-radius:8px;background:<?= $isDark ? '#1B1C1D' : '#FAF6F0' ?>;border:1px solid var(--adm-border);max-width:220px;">
                  <?= $b['badge_html'] ?>
                </div>
              </td>
              <td style="min-width:170px;">
                <form id="<?= View::e($formId) ?>" action="<?= View::e(Url::to('/admin/badges/' . (int) $b['id'] . '/update')) ?>" method="post">
                  <?= Security::csrfField() ?>
                  <input type="text" name="label" value="<?= View::e($b['label']) ?>" placeholder="Label" style="margin-bottom:6px;width:100%;">
                  <div style="display:flex;gap:8px;align-items:center;">
                    <label style="font-size:12px;color:var(--adm-muted);">Order</label>
                    <input type="number" name="sort_order" value="<?= (int) $b['sort_order'] ?>" style="width:70px;">
                    <label style="font-size:12px;">
                      <input type="checkbox" name="is_active" value="1" <?= !empty($b['is_active']) ? 'checked' : '' ?>> Active
                    </label>
                  </div>
                </form>
              </td>
              <td style="min-width:320px;">
                <textarea
                  form="<?= View::e($formId) ?>"
                  name="badge_html"
                  rows="3"
                  style="width:100%;font-family:monospace;font-size:12px;"><?= View::e($b['badge_html']) ?></textarea>
              </td>
              <td>
                <span class="badge <?= !empty($b['is_active']) ? 'badge-verified' : 'badge-coming' ?>">
                  <?= !empty($b['is_active']) ? 'Active' : 'Hidden' ?>
                </span>
              </td>
              <td style="white-space:nowrap;">
                <button type="submit" form="<?= View::e($formId) ?>" class="btn btn-primary btn-sm">Save</button>
                <form action="<?= View::e(Url::to('/admin/badges/' . (int) $b['id'] . '/toggle')) ?>" method="post" style="display:inline;">
                  <?= Security::csrfField() ?>
                  <button type="submit" class="btn btn-secondary btn-sm"><?= !empty($b['is_active']) ? 'Hide' : 'Show' ?></button>
                </form>
                <form action="<?= View::e(Url::to('/admin/badges/' . (int) $b['id'] . '/delete')) ?>" method="post" style="display:inline;">
                  <?= Security::csrfField() ?>
                  <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete this trust badge?')">Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
