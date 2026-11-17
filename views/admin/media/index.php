<?php
use App\Core\Security;
use App\Core\Url;
use App\Core\View;
?>
<div class="adm-card">
  <h2 style="margin-top:0;font-size:1.15rem;">Upload Image (Max 5MB: JPG, PNG, WebP, GIF)</h2>
  <form action="<?= View::e(Url::to('/admin/upload-image')) ?>" method="post" enctype="multipart/form-data" style="display:flex;flex-wrap:wrap;gap:12px;align-items:end;">
    <?= Security::csrfField() ?>
    <input type="hidden" name="redirect_to_media" value="1">
    <div class="field" style="margin:0;flex:1;min-width:220px;">
      <label for="media-file">Select Image</label>
      <input id="media-file" type="file" name="file" accept="image/jpeg,image/png,image/webp,image/gif" required>
    </div>
    <div class="field" style="margin:0;flex:1;min-width:220px;">
      <label for="media-alt">Alt Text (for SEO)</label>
      <input id="media-alt" type="text" name="alt_text" placeholder="Descriptive GTA 6 alt text">
    </div>
    <button type="submit" class="btn btn-primary">Upload Image</button>
  </form>
</div>

<div class="adm-card">
  <h2 style="margin-top:0;font-size:1.15rem;">Uploaded Media Library</h2>
  <?php if (empty($items)): ?>
    <p style="color:var(--adm-muted);">No images uploaded yet. Images uploaded inside TinyMCE also appear here automatically.</p>
  <?php else: ?>
    <div class="media-grid">
      <?php foreach ($items as $item): ?>
        <div class="media-tile">
          <img src="<?= View::e($item['url_path']) ?>" alt="<?= View::e($item['alt_text'] ?: $item['original_name']) ?>">
          <div class="media-tile-meta">
            <div style="font-weight:700;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= View::e($item['original_name']) ?>">
              <?= View::e($item['original_name']) ?>
            </div>
            <div style="color:var(--adm-muted);margin:4px 0 8px;"><?= round(((int) $item['file_size']) / 1024, 1) ?> KB</div>
            <div style="display:flex;gap:6px;">
              <button type="button" class="btn btn-secondary btn-sm" onclick="navigator.clipboard.writeText('<?= View::e($item['filepath']) ?>');this.textContent='Copied!';">Copy Path</button>
              <form action="<?= View::e(Url::to('/admin/media/' . $item['id'] . '/delete')) ?>" method="post">
                <?= Security::csrfField() ?>
                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete file permanently?')">✕</button>
              </form>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
