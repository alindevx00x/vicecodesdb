<?php
use App\Core\Url;
use App\Core\View;

if (!empty($breadcrumbs)):
?>
<nav class="breadcrumbs" aria-label="Breadcrumb">
  <?php foreach ($breadcrumbs as $i => $bc): ?>
    <?php if ($i > 0): ?><span aria-hidden="true">/</span><?php endif; ?>
    <?php if ($i === count($breadcrumbs) - 1): ?>
      <span aria-current="page"><?= View::e($bc['label']) ?></span>
    <?php else: ?>
      <a href="<?= View::e(Url::to($bc['url'])) ?>"><?= View::e($bc['label']) ?></a>
    <?php endif; ?>
  <?php endforeach; ?>
</nav>
<?php endif; ?>
