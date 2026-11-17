<?php
use App\Core\Security;
use App\Core\Url;
use App\Core\View;

$isEdit = !empty($cheat);
$action = $isEdit ? Url::to('/admin/cheats/' . $cheat['id'] . '/update') : Url::to('/admin/cheats/store');
$platforms = $cheat['platforms'] ?? [];
$faqs = json_decode((string) ($cheat['faqs_json'] ?? '[]'), true) ?: [];
$changelog = json_decode((string) ($cheat['changelog_json'] ?? '[]'), true) ?: [];
?>
<div id="vc-draft-restore-banner" class="draft-banner" hidden>
  <span>An unsaved local draft was found in your browser for this cheat.</span>
  <div style="display:flex;gap:8px;">
    <button type="button" id="vc-draft-restore-btn" class="btn btn-primary btn-sm">Restore Draft</button>
    <button type="button" id="vc-draft-discard-btn" class="btn btn-secondary btn-sm">Discard</button>
  </div>
</div>

<form action="<?= View::e($action) ?>" method="post">
  <?= Security::csrfField() ?>
  <div class="adm-form-grid adm-form-2col">
    <div>
      <div class="adm-card">
        <div class="field">
          <label for="title">Cheat Title *</label>
          <input id="title" type="text" name="title" required data-slug-source value="<?= View::e($cheat['title'] ?? '') ?>">
        </div>
        <div class="field">
          <label for="slug">URL Slug</label>
          <input id="slug" type="text" name="slug" data-slug-target value="<?= View::e($cheat['slug'] ?? '') ?>">
        </div>
        <div class="field">
          <label for="short_description">Short Description *</label>
          <textarea id="short_description" name="short_description" rows="2" required><?= View::e($cheat['short_description'] ?? '') ?></textarea>
        </div>
        <div class="field">
          <label for="effects">Effects Summary</label>
          <input id="effects" type="text" name="effects" value="<?= View::e($cheat['effects'] ?? '') ?>" placeholder="e.g. Grants 5 minutes of immunity to bullets &amp; explosions.">
        </div>
        <div class="field">
          <label for="verification_notes">Editorial Verification Notes</label>
          <textarea id="verification_notes" name="verification_notes" rows="2"><?= View::e($cheat['verification_notes'] ?? '') ?></textarea>
        </div>
        <div class="field">
          <label for="cheat-content">Full Guide &amp; Details (TinyMCE)</label>
          <textarea id="cheat-content" name="content" class="js-tinymce"><?= View::e($cheat['content'] ?? '') ?></textarea>
        </div>
      </div>

      <!-- Platform Codes -->
      <div class="adm-card">
        <h3 style="margin-top:0;">Platform Cheat Sequences (PS5 / Xbox / PC)</h3>
        <?php
          $quickTokens = [
            'ps5'  => ['Triangle', 'Circle', 'Cross', 'Square', 'L1', 'L2', 'R1', 'R2', 'Left', 'Right', 'Up', 'Down'],
            'xbox' => ['Y', 'B', 'A', 'X', 'LB', 'LT', 'RB', 'RT', 'Left', 'Right', 'Up', 'Down'],
            'pc'   => [],
          ];
          foreach (['ps5' => 'PS5 DualSense', 'xbox' => 'Xbox Series X|S', 'pc' => 'PC'] as $pKey => $pLabel):
            $pData = $platforms[$pKey] ?? [];
            $inputId = 'plat-seq-' . $pKey;
        ?>
          <div style="padding:12px;border:1px solid var(--adm-border);border-radius:8px;margin-bottom:12px;">
            <strong style="color:var(--adm-pink);"><?= View::e($pLabel) ?></strong>
            <?php if (!empty($quickTokens[$pKey])): ?>
              <div class="seq-toolbar" data-seq-target="<?= View::e($inputId) ?>">
                <?php foreach ($quickTokens[$pKey] as $tok): ?>
                  <button type="button" class="seq-chip" data-seq-token="<?= View::e($tok) ?>">+ <?= View::e($tok) ?></button>
                <?php endforeach; ?>
                <button type="button" class="seq-chip seq-chip-clear" data-seq-clear="1">Clear</button>
              </div>
            <?php endif; ?>
            <div class="field" style="margin-top:8px;">
              <label for="<?= View::e($inputId) ?>">Button / Command Sequence (comma-separated buttons show SVG keycaps)</label>
              <input id="<?= View::e($inputId) ?>" type="text" name="platforms[<?= $pKey ?>][code_sequence]" value="<?= View::e($pData['code_sequence'] ?? '') ?>" placeholder="e.g. R1, R1, Circle, R2, Left, Right">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1.5fr;gap:10px;">
              <div class="field">
                <label>In-Game Phone Number (Optional)</label>
                <input type="text" name="platforms[<?= $pKey ?>][cell_number]" value="<?= View::e($pData['cell_number'] ?? '') ?>" placeholder="e.g. 1-999-...">
              </div>
              <div class="field">
                <label>Platform Notes</label>
                <input type="text" name="platforms[<?= $pKey ?>][notes]" value="<?= View::e($pData['notes'] ?? '') ?>">
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- FAQ & Changelog Builders -->
      <div class="adm-card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
          <h3 style="margin:0;">Cheat FAQs (Generates FAQPage JSON-LD)</h3>
          <button type="button" id="add-faq-row" class="btn btn-secondary btn-sm">+ Add FAQ</button>
        </div>
        <div id="faq-rows">
          <?php foreach ($faqs as $faq): ?>
            <div class="faq-builder-row" style="display:grid;gap:8px;padding:12px;border:1px solid var(--adm-border);border-radius:8px;margin-bottom:10px;">
              <input type="text" name="faq_q[]" value="<?= View::e($faq['q'] ?? '') ?>" placeholder="Question">
              <textarea name="faq_a[]" rows="2" placeholder="Direct concise answer..."><?= View::e($faq['a'] ?? '') ?></textarea>
              <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.remove()">Remove FAQ</button>
            </div>
          <?php endforeach; ?>
        </div>

        <hr style="border-color:var(--adm-border);margin:18px 0;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
          <h3 style="margin:0;">Verification Changelog</h3>
          <button type="button" id="add-cl-row" class="btn btn-secondary btn-sm">+ Add Log Entry</button>
        </div>
        <div id="cl-rows">
          <?php foreach ($changelog as $cl): ?>
            <div style="display:grid;grid-template-columns:140px 1fr auto;gap:8px;margin-bottom:8px;">
              <input type="text" name="cl_date[]" value="<?= View::e($cl['date'] ?? '') ?>">
              <input type="text" name="cl_note[]" value="<?= View::e($cl['note'] ?? '') ?>">
              <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.remove()">✕</button>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <div>
      <div class="adm-card">
        <h3 style="margin-top:0;">Publishing &amp; Verification</h3>
        <div class="field">
          <label for="status">Verification Status</label>
          <select id="status" name="status">
            <?php foreach (['coming_soon' => 'Coming at Launch', 'verified' => 'Verified', 'unconfirmed' => 'Unconfirmed', 'debunked' => 'Debunked'] as $k => $lbl): ?>
              <option value="<?= $k ?>" <?= ($cheat['status'] ?? 'coming_soon') === $k ? 'selected' : '' ?>><?= $lbl ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
          <div class="field">
            <label for="game_patch">Game Patch</label>
            <input id="game_patch" type="text" name="game_patch" value="<?= View::e($cheat['game_patch'] ?? 'v1.00') ?>" placeholder="v1.00">
          </div>
          <div class="field">
            <label for="sort_order">Sort Order</label>
            <input id="sort_order" type="number" name="sort_order" value="<?= (int) ($cheat['sort_order'] ?? 0) ?>">
          </div>
        </div>
        <div class="field">
          <label for="category_id">Category</label>
          <select id="category_id" name="category_id">
            <option value="">-- Select Category --</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= (int) $cat['id'] ?>" <?= (int) ($cheat['category_id'] ?? 0) === (int) $cat['id'] ? 'selected' : '' ?>>
                <?= View::e($cat['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field">
          <label for="tags">Topic Tags (comma-separated)</label>
          <input id="tags" type="text" name="tags" value="<?= View::e($tagsCsv ?? '') ?>" placeholder="PS5 DualSense, Leonida Vehicles, God Mode">
        </div>
        <div class="field">
          <label><input type="checkbox" name="is_featured" value="1" <?= !empty($cheat['is_featured']) ? 'checked' : '' ?>> Feature on Homepage</label>
        </div>
        <?php if (!empty($cheat['needs_retest'])): ?>
          <div class="flash flash-error" style="padding:10px 12px;margin-bottom:12px;">
            Flagged by players (<?= (int) ($cheat['votes_broken'] ?? 0) ?> broken reports).
            <label style="display:block;margin-top:6px;font-weight:400;">
              <input type="checkbox" name="clear_retest" value="1" checked> Clear re-test flag when saving as Verified
            </label>
          </div>
        <?php endif; ?>
        <div class="field">
          <label><input type="checkbox" name="needs_retest" value="1" <?= !empty($cheat['needs_retest']) ? 'checked' : '' ?>> Flag for Editor Re-Test</label>
        </div>
        <div class="field">
          <label for="last_verified_at">Last Verified Timestamp</label>
          <input id="last_verified_at" type="text" name="last_verified_at" value="<?= View::e($cheat['last_verified_at'] ?? date('Y-m-d H:i:s')) ?>">
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;">Save Cheat Code</button>
      </div>

      <div class="adm-card">
        <h3 style="margin-top:0;">SEO &amp; OpenGraph</h3>
        <div class="field">
          <label for="focus_keyword">Focus Keyword</label>
          <input id="focus_keyword" type="text" name="focus_keyword" value="<?= View::e($cheat['focus_keyword'] ?? '') ?>">
        </div>
        <div class="field">
          <label for="meta_title">Meta Title</label>
          <input id="meta_title" type="text" name="meta_title" value="<?= View::e($cheat['meta_title'] ?? '') ?>">
        </div>
        <div class="field">
          <label for="meta_description">Meta Description</label>
          <textarea id="meta_description" name="meta_description" rows="3"><?= View::e($cheat['meta_description'] ?? '') ?></textarea>
        </div>
        <div class="field">
          <label for="og_image">OG Image URL (leave blank for auto-generated PNG card)</label>
          <div style="display:flex;gap:8px;">
            <input id="og_image" type="text" name="og_image" value="<?= View::e($cheat['og_image'] ?? '') ?>" placeholder="Auto /og/cheat/{slug}.png">
            <button type="button" class="btn btn-secondary btn-sm js-open-media-picker" data-media-target="#og_image">Media</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</form>
