<?php
use App\Core\Security;
use App\Core\Url;
use App\Core\View;
?>
<div class="adm-card">
  <div style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:12px;margin-bottom:16px;">
    <input id="admin-table-search" type="search" placeholder="Filter cheats by title, category, or status..." style="max-width:340px;padding:9px 12px;border-radius:6px;border:1px solid var(--color-6);background:var(--color-13);color:var(--color-1);">
    <a class="btn btn-primary" href="<?= View::e(Url::to('/admin/cheats/create')) ?>">+ Add Cheat Code</a>
  </div>

  <form action="<?= View::e(Url::to('/admin/cheats/bulk')) ?>" method="post">
    <?= Security::csrfField() ?>
    <div style="display:flex;gap:8px;align-items:center;margin-bottom:12px;">
      <select name="bulk_status" style="padding:7px 10px;border-radius:6px;background:var(--color-13);color:var(--color-1);border:1px solid var(--color-6);">
        <option value="">Bulk Status Change...</option>
        <option value="verified">Mark Verified</option>
        <option value="unconfirmed">Mark Unconfirmed</option>
        <option value="debunked">Mark Debunked</option>
        <option value="coming_soon">Mark Coming at Launch</option>
      </select>
      <button type="submit" class="btn btn-secondary btn-sm">Apply</button>
    </div>

    <div class="adm-table-wrap">
      <table class="adm-table">
        <thead>
          <tr>
            <th><input type="checkbox" id="bulk-select-all"></th>
            <th>Title</th>
            <th>Category</th>
            <th>Status</th>
            <th>PS5 / Xbox</th>
            <th>Views</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($cheats as $c): ?>
            <tr>
              <td><input type="checkbox" name="ids[]" value="<?= (int) $c['id'] ?>"></td>
              <td>
                <strong><?= View::e($c['title']) ?></strong>
                <div style="font-size:0.78rem;color:var(--adm-muted);">/cheats/<?= View::e($c['slug']) ?></div>
              </td>
              <td><?= View::e($c['category_name'] ?? 'Uncategorized') ?></td>
              <td><span class="badge badge-<?= View::e($c['status']) ?>"><?= View::e(str_replace('_', ' ', $c['status'])) ?></span></td>
              <td style="font-size:0.8rem;color:var(--adm-muted);">
                <?= View::e(mb_strimwidth($c['platforms']['ps5']['code_sequence'] ?? '-', 0, 28, '...')) ?>
              </td>
              <td><?= number_format((int) $c['views']) ?></td>
              <td style="white-space:nowrap;">
                <a class="btn btn-secondary btn-sm" href="<?= View::e(Url::to('/admin/cheats/' . $c['id'] . '/edit')) ?>">Edit</a>
                <a class="btn btn-secondary btn-sm" href="<?= View::e(Url::to('/cheats/' . $c['slug'])) ?>" target="_blank">View</a>
                <button type="submit" formaction="<?= View::e(Url::to('/admin/cheats/' . $c['id'] . '/delete')) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this cheat?')">Delete</button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </form>
</div>
