<?php
use App\Core\Security;
use App\Core\Url;
use App\Core\View;

$isEdit = !empty($post);
$action = $isEdit ? Url::to('/admin/blog/' . $post['id'] . '/update') : Url::to('/admin/blog/store');
$faqs = json_decode((string) ($post['faqs_json'] ?? '[]'), true) ?: [];
$pubVal = !empty($post['published_at']) ? date('Y-m-d\TH:i', strtotime((string) $post['published_at'])) : date('Y-m-d\TH:i');
?>
<div id="vc-draft-restore-banner" class="draft-banner" hidden>
  <span>An unsaved local draft was found in your browser for this article.</span>
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
          <label for="title">Post Title *</label>
          <input id="title" type="text" name="title" required data-slug-source value="<?= View::e($post['title'] ?? '') ?>">
        </div>
        <div class="field">
          <label for="slug">URL Slug</label>
          <input id="slug" type="text" name="slug" data-slug-target value="<?= View::e($post['slug'] ?? '') ?>">
        </div>
        <div class="field">
          <label for="quick_answer">AEO Quick Answer / Key Takeaway (40-60 words for Google AI Overviews &amp; Perplexity)</label>
          <textarea id="quick_answer" name="quick_answer" rows="3"><?= View::e($post['quick_answer'] ?? '') ?></textarea>
        </div>
        <div class="field">
          <label for="excerpt">Excerpt</label>
          <textarea id="excerpt" name="excerpt" rows="2"><?= View::e($post['excerpt'] ?? '') ?></textarea>
        </div>
        <div class="field">
          <label for="blog-content">Article Content (TinyMCE)</label>
          <textarea id="blog-content" name="content" class="js-tinymce"><?= View::e($post['content'] ?? '') ?></textarea>
        </div>
      </div>

      <div class="adm-card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
          <h3 style="margin:0;">Article FAQs (FAQPage Schema)</h3>
          <button type="button" id="add-faq-row" class="btn btn-secondary btn-sm">+ Add FAQ</button>
        </div>
        <div id="faq-rows">
          <?php foreach ($faqs as $faq): ?>
            <div class="faq-builder-row" style="display:grid;gap:8px;padding:12px;border:1px solid var(--adm-border);border-radius:8px;margin-bottom:10px;">
              <input type="text" name="faq_q[]" value="<?= View::e($faq['q'] ?? '') ?>" placeholder="Question">
              <textarea name="faq_a[]" rows="2"><?= View::e($faq['a'] ?? '') ?></textarea>
              <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.remove()">Remove FAQ</button>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <div>
      <div class="adm-card">
        <h3 style="margin-top:0;">Publish Settings</h3>
        <div class="field">
          <label for="status">Status</label>
          <select id="status" name="status">
            <?php foreach (['published' => 'Published', 'draft' => 'Draft', 'scheduled' => 'Scheduled'] as $k => $lbl): ?>
              <option value="<?= $k ?>" <?= ($post['status'] ?? 'draft') === $k ? 'selected' : '' ?>><?= $lbl ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field">
          <label for="published_at">Publish Date</label>
          <input id="published_at" type="datetime-local" name="published_at" value="<?= View::e($pubVal) ?>">
        </div>
        <div class="field">
          <label for="category_id">Category</label>
          <select id="category_id" name="category_id">
            <option value="">-- Select Category --</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= (int) $cat['id'] ?>" <?= (int) ($post['category_id'] ?? 0) === (int) $cat['id'] ? 'selected' : '' ?>>
                <?= View::e($cat['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field">
          <label for="tags">Topic Tags (comma-separated)</label>
          <input id="tags" type="text" name="tags" value="<?= View::e($tagsCsv ?? '') ?>" placeholder="Launch Guide, Trophies &amp; Achievements">
        </div>
        <div class="field">
          <label for="featured_image">Featured Image URL</label>
          <div style="display:flex;gap:8px;">
            <input id="featured_image" type="text" name="featured_image" value="<?= View::e($post['featured_image'] ?? '') ?>" placeholder="/uploads/editor/...">
            <button type="button" class="btn btn-secondary btn-sm js-open-media-picker" data-media-target="#featured_image">Media</button>
          </div>
        </div>
        <div class="field">
          <label for="featured_image_alt">Featured Image Alt Text (SEO)</label>
          <input id="featured_image_alt" type="text" name="featured_image_alt" value="<?= View::e($post['featured_image_alt'] ?? '') ?>">
        </div>
        <div style="display:grid;gap:8px;">
          <button type="submit" class="btn btn-primary" style="width:100%;">Save Blog Post</button>
          <?php if ($isEdit && !empty($post['slug'])): ?>
            <a class="btn btn-secondary" style="width:100%;" href="<?= View::e(Url::to('/blog/' . $post['slug'] . '?preview=1')) ?>" target="_blank">Preview Draft ↗</a>
          <?php endif; ?>
        </div>
      </div>

      <div class="adm-card">
        <h3 style="margin-top:0;">SEO Meta Fields</h3>
        <div class="field">
          <label for="focus_keyword">Focus Keyword</label>
          <input id="focus_keyword" type="text" name="focus_keyword" value="<?= View::e($post['focus_keyword'] ?? '') ?>">
        </div>
        <div class="field">
          <label for="meta_title">Meta Title</label>
          <input id="meta_title" type="text" name="meta_title" value="<?= View::e($post['meta_title'] ?? '') ?>">
        </div>
        <div class="field">
          <label for="meta_description">Meta Description</label>
          <textarea id="meta_description" name="meta_description" rows="3"><?= View::e($post['meta_description'] ?? '') ?></textarea>
        </div>
      </div>
    </div>
  </div>
</form>
