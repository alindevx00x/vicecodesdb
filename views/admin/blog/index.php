<?php
use App\Core\Security;
use App\Core\Url;
use App\Core\View;
?>
<div class="adm-card">
  <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:16px;">
    <input id="admin-table-search" type="search" placeholder="Filter blog posts..." style="max-width:340px;padding:9px 12px;border-radius:6px;border:1px solid var(--color-6);background:var(--color-13);color:var(--color-1);">
    <a class="btn btn-primary" href="<?= View::e(Url::to('/admin/blog/create')) ?>">+ New Blog Post</a>
  </div>

  <div class="adm-table-wrap">
    <table class="adm-table">
      <thead>
        <tr><th>Title</th><th>Category</th><th>Status</th><th>Published</th><th>Views</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php foreach ($posts as $p): ?>
          <tr>
            <td>
              <strong><?= View::e($p['title']) ?></strong>
              <div style="font-size:0.78rem;color:var(--adm-muted);">/blog/<?= View::e($p['slug']) ?></div>
            </td>
            <td><?= View::e($p['category_name'] ?? '-') ?></td>
            <td><span class="badge badge-<?= View::e($p['status']) ?>"><?= View::e($p['status']) ?></span></td>
            <td><?= View::e($p['published_at'] ? date('M j, Y', strtotime((string) $p['published_at'])) : '-') ?></td>
            <td><?= number_format((int) $p['views']) ?></td>
            <td style="white-space:nowrap;">
              <a class="btn btn-secondary btn-sm" href="<?= View::e(Url::to('/admin/blog/' . $p['id'] . '/edit')) ?>">Edit</a>
              <?php if ($p['status'] === 'published'): ?>
                <a class="btn btn-secondary btn-sm" href="<?= View::e(Url::to('/blog/' . $p['slug'])) ?>" target="_blank">View</a>
              <?php endif; ?>
              <form style="display:inline;" action="<?= View::e(Url::to('/admin/blog/' . $p['id'] . '/delete')) ?>" method="post">
                <?= Security::csrfField() ?>
                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete post?')">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
