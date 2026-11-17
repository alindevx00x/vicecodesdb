<?php
use App\Core\Security;
use App\Core\Url;
use App\Core\View;
?>
<div class="adm-form-grid adm-form-2col">
  <div class="adm-card">
    <h2 style="margin-top:0;font-size:1.15rem;">All Categories</h2>
    <div class="adm-table-wrap">
      <table class="adm-table">
        <thead>
          <tr><th>Name</th><th>Type</th><th>Slug</th><th>Order</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php foreach ($categories as $cat): ?>
            <tr>
              <td><strong><?= View::e($cat['name']) ?></strong></td>
              <td><span class="badge badge-coming_soon"><?= View::e($cat['type']) ?></span></td>
              <td><?= View::e($cat['slug']) ?></td>
              <td><?= (int) $cat['sort_order'] ?></td>
              <td>
                <form action="<?= View::e(Url::to('/admin/categories/' . $cat['id'] . '/delete')) ?>" method="post" style="display:inline;">
                  <?= Security::csrfField() ?>
                  <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete category?')">Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="adm-card">
    <h2 style="margin-top:0;font-size:1.15rem;">Add Category</h2>
    <form action="<?= View::e(Url::to('/admin/categories/save')) ?>" method="post">
      <?= Security::csrfField() ?>
      <div class="field">
        <label for="cat-name">Name *</label>
        <input id="cat-name" type="text" name="name" required data-slug-source>
      </div>
      <div class="field">
        <label for="cat-slug">Slug</label>
        <input id="cat-slug" type="text" name="slug" data-slug-target>
      </div>
      <div class="field">
        <label for="cat-type">Type</label>
        <select id="cat-type" name="type">
          <option value="cheat">Cheat Category</option>
          <option value="blog">Blog Category</option>
        </select>
      </div>
      <div class="field">
        <label for="cat-icon">Icon Key (crosshair, shield, car, star, bolt, sun)</label>
        <input id="cat-icon" type="text" name="icon" value="bolt">
      </div>
      <div class="field">
        <label for="cat-desc">Description</label>
        <textarea id="cat-desc" name="description" rows="2"></textarea>
      </div>
      <div class="field">
        <label for="cat-sort">Sort Order</label>
        <input id="cat-sort" type="number" name="sort_order" value="0">
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;">Create Category</button>
    </form>
  </div>
</div>
