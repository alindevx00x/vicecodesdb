<?php
use App\Core\Url;
use App\Core\View;

$shareUrl = urlencode(Url::full('/blog/' . $post['slug']));
$shareTitle = urlencode($post['title']);
?>
<article class="section" style="padding-top:32px;">
  <div class="container" style="max-width:860px;">
    <?php require dirname(__DIR__, 2) . '/partials/breadcrumbs.php'; ?>

    <?php if (!empty($isPreview) && ($post['status'] ?? '') !== 'published'): ?>
      <div class="flash flash-error" style="margin-bottom:18px;">
        <strong>ADMIN DRAFT PREVIEW:</strong> This post is currently <em><?= View::e($post['status']) ?></em> and is not visible to public visitors.
      </div>
    <?php endif; ?>

    <header style="margin-bottom:22px;">
      <?php if (!empty($post['category_name'])): ?>
        <a class="cheat-cat-tag" href="<?= View::e(Url::to('/blog/category/' . $post['category_slug'])) ?>">
          <?= View::e($post['category_name']) ?>
        </a>
      <?php endif; ?>
      <h1 style="margin:8px 0 12px;"><?= View::e($post['title']) ?></h1>
      <div class="blog-meta" style="font-size:0.9rem;">
        By <strong><?= View::e($post['author_name'] ?? 'ViceCodes Editorial Team') ?></strong> ·
        Published <?= View::e(date('M j, Y', strtotime((string) ($post['published_at'] ?? $post['created_at'])))) ?> ·
        Updated <?= View::e(date('M j, Y', strtotime((string) $post['updated_at']))) ?> ·
        <?= (int) ($post['reading_time'] ?? 3) ?> min read
      </div>
    </header>

    <?php if (!empty($post['featured_image'])): ?>
      <figure style="margin:0 0 24px;border-radius:14px;overflow:hidden;border:1px solid var(--border-color);">
        <img src="<?= View::e(Url::to($post['featured_image'])) ?>" alt="<?= View::e($post['featured_image_alt'] ?: $post['title']) ?>">
      </figure>
    <?php endif; ?>

    <!-- AEO / GEO Quick Answer Box -->
    <?php if (!empty($post['quick_answer'])): ?>
      <div class="quick-answer-box">
        <div class="quick-answer-label">Quick Answer / Key Takeaway</div>
        <p style="margin:0;"><?= View::e($post['quick_answer']) ?></p>
      </div>
    <?php endif; ?>

    <!-- Body Content (TinyMCE + Auto-Linked) -->
    <div class="glass-card prose" style="padding:26px;">
      <?= $post['content'] ?>
    </div>

    <?php if (!empty($tags)): ?>
      <div style="display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin-top:20px;">
        <span class="eyebrow" style="margin-right:4px;">Topics:</span>
        <?php foreach ($tags as $t): ?>
          <a class="tag-pill" href="<?= View::e(Url::to('/tag/' . $t['slug'])) ?>">#<?= View::e($t['name']) ?></a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- Social Share -->
    <div style="display:flex;flex-wrap:wrap:align-items:center;gap:10px;margin:24px 0;">
      <strong style="color:var(--text-secondary);font-size:0.9rem;">Share Guide:</strong>
      <a class="btn btn-outline btn-sm" target="_blank" rel="noopener" href="https://twitter.com/intent/tweet?url=<?= $shareUrl ?>&text=<?= $shareTitle ?>">Share on X</a>
      <a class="btn btn-outline btn-sm" target="_blank" rel="noopener" href="https://www.reddit.com/submit?url=<?= $shareUrl ?>&title=<?= $shareTitle ?>">Share on Reddit</a>
      <button type="button" class="btn btn-outline btn-sm" data-copy-text="<?= View::e(Url::full('/blog/' . $post['slug'])) ?>">Copy Link</button>
    </div>

    <?php if (!empty($faqs)): ?>
      <section style="margin:32px 0;">
        <h2>Frequently Asked Questions</h2>
        <?php require dirname(__DIR__, 2) . '/partials/faq-accordion.php'; ?>
      </section>
    <?php endif; ?>

    <?php if (!empty($related)): ?>
      <section style="margin-top:40px;">
        <h2>Related Guides</h2>
        <div class="grid-blog" style="margin-top:16px;">
          <?php foreach ($related as $post): ?>
            <?php require dirname(__DIR__, 2) . '/partials/blog-card.php'; ?>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>
  </div>
</article>
