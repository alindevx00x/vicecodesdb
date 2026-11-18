<?php
use App\Core\Security;
use App\Core\Url;
use App\Core\View;
?>
<div class="adm-form-grid adm-form-2col">
  <div class="adm-card">
    <h3 style="margin-top:0;">Editor Profile</h3>
    <p style="color:var(--adm-muted);font-size:13px;margin-top:0;">
      Your display name and bio appear on published Case Files &amp; Guides for Google E-E-A-T author attribution.
    </p>
    <form action="<?= View::e(Url::to('/admin/profile/update')) ?>" method="post">
      <?= Security::csrfField() ?>
      <div class="field">
        <label for="profile_email">Account Email</label>
        <input id="profile_email" type="email" value="<?= View::e($adminUser['email'] ?? '') ?>" disabled style="opacity:0.7;">
      </div>
      <div class="field">
        <label for="profile_name">Display Name *</label>
        <input id="profile_name" type="text" name="name" required value="<?= View::e($adminUser['name'] ?? '') ?>">
      </div>
      <div class="field">
        <label for="profile_bio">Author Bio</label>
        <textarea id="profile_bio" name="bio" rows="4" placeholder="Senior GTA 6 verification editor..."><?= View::e($adminUser['bio'] ?? '') ?></textarea>
      </div>
      <button type="submit" class="btn btn-primary">Save Profile</button>
    </form>
  </div>

  <div class="adm-card">
    <h3 style="margin-top:0;">Change Password</h3>
    <p style="color:var(--adm-muted);font-size:13px;margin-top:0;">
      Update your CMS login password directly without needing phpMyAdmin. Minimum 8 characters.
    </p>
    <form action="<?= View::e(Url::to('/admin/profile/password')) ?>" method="post">
      <?= Security::csrfField() ?>
      <div class="field">
        <label for="current_password">Current Password *</label>
        <input id="current_password" type="password" name="current_password" required autocomplete="current-password">
      </div>
      <div class="field">
        <label for="new_password">New Password * (min 8 chars)</label>
        <input id="new_password" type="password" name="new_password" required minlength="8" autocomplete="new-password">
      </div>
      <div class="field">
        <label for="confirm_password">Confirm New Password *</label>
        <input id="confirm_password" type="password" name="confirm_password" required minlength="8" autocomplete="new-password">
      </div>
      <button type="submit" class="btn btn-primary">Update Password</button>
    </form>
  </div>
</div>
