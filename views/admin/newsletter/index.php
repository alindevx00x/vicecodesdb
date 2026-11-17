<?php
use App\Core\Security;
use App\Core\Url;
use App\Core\View;
?>
<div class="adm-card">
  <div style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:12px;margin-bottom:16px;">
    <input id="admin-table-search" type="search" placeholder="Search subscribers..." style="max-width:300px;padding:9px 12px;border-radius:6px;border:1px solid var(--color-6);background:var(--color-13);color:var(--color-1);">
    <a class="btn btn-primary" href="<?= View::e(Url::to('/admin/newsletter/export')) ?>">Export CSV</a>
  </div>

  <div class="adm-table-wrap">
    <table class="adm-table">
      <thead>
        <tr><th>Email</th><th>Name</th><th>Status</th><th>Confirmed</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php if (empty($subscribers)): ?>
          <tr><td colspan="5" style="color:var(--adm-muted);">No newsletter subscribers yet.</td></tr>
        <?php else: ?>
          <?php foreach ($subscribers as $sub): ?>
            <tr>
              <td><strong><?= View::e($sub['email']) ?></strong></td>
              <td><?= View::e($sub['name'] ?: '-') ?></td>
              <td><span class="badge badge-<?= View::e($sub['status']) ?>"><?= View::e($sub['status']) ?></span></td>
              <td><?= View::e($sub['confirmed_at'] ? date('M j, Y H:i', strtotime((string) $sub['confirmed_at'])) : 'Pending') ?></td>
              <td style="white-space:nowrap;">
                <form action="<?= View::e(Url::to('/admin/newsletter/' . $sub['id'] . '/status')) ?>" method="post" style="display:inline;">
                  <?= Security::csrfField() ?>
                  <input type="hidden" name="status" value="<?= $sub['status'] === 'subscribed' ? 'unsubscribed' : 'subscribed' ?>">
                  <button type="submit" class="btn btn-secondary btn-sm">
                    <?= $sub['status'] === 'subscribed' ? 'Unsubscribe' : 'Mark Subscribed' ?>
                  </button>
                </form>
                <form action="<?= View::e(Url::to('/admin/newsletter/' . $sub['id'] . '/delete')) ?>" method="post" style="display:inline;">
                  <?= Security::csrfField() ?>
                  <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete subscriber?')">Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
