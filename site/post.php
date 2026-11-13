<?php
require __DIR__ . '/_rb.php';
$id = isset($_GET['id']) ? preg_replace('/[^a-f0-9]/', '', $_GET['id']) : '';
$item = null;
foreach (rb_items() as $i) { if (($i['id'] ?? '') === $id) { $item = $i; break; } }
if (!$item) {
  http_response_code(404);
  rb_head('Post not found | RadioBIM', 'This post is not on RadioBIM.', RB_SITE . '/posts', '<meta name="robots" content="noindex">');
  echo '<h1>البوست ده مش موجود</h1><p class="en">This post is not on RadioBIM. <a href="/posts">See all posts</a></p>';
  rb_foot(); exit;
}
$S = rb_sections(); $sec = $S[$item['c']] ?? ['', ''];
$t_en = rb_t($item); $t_ar = $item['title'] ?? '';
$title = $t_en . ' | RadioBIM';
$desc = trim(($item['desc_en'] ?? '') . ' ' . ($item['desc'] ?? ''));
$canon = RB_SITE . '/p/' . $item['id'];
$ld = [
  '@context' => 'https://schema.org', '@type' => 'Article',
  'headline' => $t_en, 'alternativeHeadline' => $t_ar,
  'description' => $desc, 'url' => $canon, 'mainEntityOfPage' => $canon,
  'datePublished' => $item['date'] ?? null, 'dateModified' => $item['added'] ?? ($item['date'] ?? null),
  'author' => ['@type' => 'Person', 'name' => trim($item['a'] ?? '', " .")],
  'publisher' => ['@type' => 'Organization', 'name' => 'RadioBIM', 'logo' => ['@type' => 'ImageObject', 'url' => RB_SITE . '/icon-512.png']],
  'image' => RB_SITE . '/og.jpg', 'keywords' => implode(', ', $item['tags'] ?? []),
  'articleSection' => $sec[1], 'inLanguage' => ['en', 'ar'], 'isBasedOn' => $item['url'] ?? null,
];
$extra = '<meta name="keywords" content="' . h(implode(', ', array_merge($item['tags'] ?? [], ['BIM', 'AEC', 'AI']))) . '">'
  . '<script type="application/ld+json">' . json_encode($ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>';
rb_head($title, $desc, $canon, $extra);
?>
<nav class="muted"><a href="/posts">كل البوستات</a> › <a href="/section/<?= h($item['c']) ?>"><?= h($sec[0]) ?></a></nav>
<h1 class="en"><?= h($t_en) ?></h1>
<p class="en muted"><?= h($item['desc_en'] ?? '') ?></p>
<h2><?= h($t_ar) ?></h2>
<p><?= h($item['desc'] ?? '') ?></p>
<p class="muted">✍️ <bdi><?= h(trim($item['a'] ?? '', " .")) ?></bdi> · <?= h($item['date'] ?? '') ?> · <?= h($sec[1]) ?></p>
<div class="tags"><?php foreach (($item['tags'] ?? []) as $t): ?><a href="/tag/<?= h(rb_slug($t)) ?>"><?= h($t) ?></a><?php endforeach; ?></div>
<div class="acts">
 <a class="btn" href="<?= h($item['url']) ?>" target="_blank" rel="noopener">اقرا البوست على LinkedIn</a>
 <?php if (!empty($item['link'])): ?><a class="btn" href="<?= h($item['link']) ?>" target="_blank" rel="nofollow noopener">افتح الأداة (<?= h($item['linkLabel'] ?? 'Link') ?>)</a><?php endif; ?>
 <a class="btn" href="/">كل الجديد على RadioBIM</a>
</div>
<?php
$rel = []; foreach (rb_items() as $i) { if ($i['id'] !== $item['id'] && $i['c'] === $item['c']) { $rel[] = $i; if (count($rel) >= 6) break; } }
if ($rel) { echo '<h2>بوستات تانية في ' . h($sec[0]) . '</h2>'; foreach ($rel as $i) rb_card($i); }
rb_foot();
