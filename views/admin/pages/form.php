<?php
use App\Core\Security;
use App\Core\Url;
use App\Core\View;

$isEdit = !empty($page);
$action = $isEdit ? Url::to('/admin/pages/' . $page['id'] . '/update') : Url::to('/admin/pages/store');
?>
<form action="<?= View::e($action) ?>" method="post">
  <?= Security::csrfField() ?>
  <div class="adm-form-grid adm-form-2col">
    <div class="adm-card">
      <div class="field">
        <label for="title">Page Title *</label>
        <input id="title" type="text" name="title" required data-slug-source value="<?= View::e($page['title'] ?? '') ?>">
      </div>
      <div class="field">
        <label for="slug">Slug</label>
        <input id="slug" type="text" name="slug" data-slug-target value="<?= View::e($page['slug'] ?? '') ?>">
      </div>
      <div class="field">
        <label for="quick_answer">Quick Answer / Summary Box (AEO)</label>
        <textarea id="quick_answer" name="quick_answer" rows="2"><?= View::e($page['quick_answer'] ?? '') ?></textarea>
      </div>
      <div class="field">
        <label for="page-content">Page Content (TinyMCE)</label>
        <textarea id="page-content" name="content" class="js-tinymce"><?= View::e($page['content'] ?? '') ?></textarea>
      </div>
    </div>

    <div>
      <div class="adm-card">
        <h3 style="margin-top:0;">Publish &amp; SEO</h3>
        <div class="field">
          <label for="status">Status</label>
          <select id="status" name="status">
            <option value="published" <?= ($page['status'] ?? 'published') === 'published' ? 'selected' : '' ?>>Published</option>
            <option value="draft" <?= ($page['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
          </select>
        </div>
        <div class="field">
          <label for="focus_keyword">Focus Keyword</label>
          <input id="focus_keyword" type="text" name="focus_keyword" value="<?= View::e($page['focus_keyword'] ?? '') ?>">
        </div>
        <div class="field">
          <label for="meta_title">Meta Title</label>
          <input id="meta_title" type="text" name="meta_title" value="<?= View::e($page['meta_title'] ?? '') ?>">
        </div>
        <div class="field">
          <label for="meta_description">Meta Description</label>
          <textarea id="meta_description" name="meta_description" rows="3"><?= View::e($page['meta_description'] ?? '') ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;">Save Static Page</button>
      </div>
    </div>
  </div>
</form>
