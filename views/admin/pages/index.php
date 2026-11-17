<?php
use App\Core\Security;
use App\Core\Url;
use App\Core\View;
?>
<div class="adm-card">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
    <h2 style="margin:0;font-size:1.15rem;">Editable Static Pages</h2>
    <a class="btn btn-primary" href="<?= View::e(Url::to('/admin/pages/create')) ?>">+ New Page</a>
  </div>
  <div class="adm-table-wrap">
    <table class="adm-table">
      <thead>
        <tr><th>Title</th><th>Slug</th><th>Status</th><th>Updated</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php foreach ($pages as $pg): ?>
          <tr>
            <td><strong><?= View::e($pg['title']) ?></strong></td>
            <td>/<?= View::e($pg['slug']) ?></td>
            <td><span class="badge badge-<?= View::e($pg['status']) ?>"><?= View::e($pg['status']) ?></span></td>
            <td><?= View::e(date('M j, Y', strtotime((string) $pg['updated_at']))) ?></td>
            <td style="white-space:nowrap;">
              <a class="btn btn-secondary btn-sm" href="<?= View::e(Url::to('/admin/pages/' . $pg['id'] . '/edit')) ?>">Edit</a>
              <a class="btn btn-secondary btn-sm" href="<?= View::e(Url::to('/' . $pg['slug'])) ?>" target="_blank">View</a>
              <form style="display:inline;" action="<?= View::e(Url::to('/admin/pages/' . $pg['id'] . '/delete')) ?>" method="post">
                <?= Security::csrfField() ?>
                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete this page?')">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
