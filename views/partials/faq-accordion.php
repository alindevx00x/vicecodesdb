<?php
use App\Core\View;

if (!empty($faqs)):
?>
<div class="faq-list">
  <?php foreach ($faqs as $idx => $faq): ?>
    <div class="glass-card faq-item <?= $idx === 0 ? 'open' : '' ?>">
      <button type="button" class="faq-btn" aria-expanded="<?= $idx === 0 ? 'true' : 'false' ?>">
        <span><?= View::e($faq['q']) ?></span>
        <span class="faq-plus" aria-hidden="true">+</span>
      </button>
      <div class="faq-panel">
        <p style="margin:0;"><?= View::e($faq['a']) ?></p>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>
