<?php
require __DIR__ . '/_rb.php';
$id = isset($_GET['id']) ? preg_replace('/[^a-f0-9]/', '', $_GET['id']) : '';
$item = null; $all = rb_items();
foreach ($all as $i) { if (($i['id'] ?? '') === $id) { $item = $i; break; } }
if (!$item) {
  http_response_code(404);
  rb_head('Post not found | RadioBIM', 'This post is not on RadioBIM.', RB_SITE . '/posts', '<meta name="robots" content="noindex">');
  echo '<h1 data-l="ar">البوست ده مش موجود</h1><h1 data-l="en">This post is not on RadioBIM</h1><p><a href="/posts">كل البوستات · All posts</a></p>';
  rb_foot(); exit;
}
$S = rb_sections(); $c = $item['c'] ?? ''; $sec = $S[$c] ?? ['', '', '']; [$bg, $ac] = rb_sec_color($c);
$t_en = rb_t($item); $t_ar = $item['title'] ?? ''; $au = rb_author($item);
$title = $t_en . ' | RadioBIM';
$desc = trim(($item['desc_en'] ?? '') . ' ' . ($item['desc'] ?? ''));
$canon = RB_SITE . '/p/' . $item['id'];
$isNew = ($item['added'] ?? '') === (rb_data()['updated'] ?? '-');
$ld = [
  '@context' => 'https://schema.org', '@type' => 'Article',
  'headline' => $t_en, 'alternativeHeadline' => $t_ar, 'description' => $desc, 'url' => $canon, 'mainEntityOfPage' => $canon,
  'datePublished' => $item['date'] ?? null, 'dateModified' => $item['added'] ?? ($item['date'] ?? null),
  'author' => ['@type' => 'Person', 'name' => $au],
  'publisher' => ['@type' => 'Organization', 'name' => 'RadioBIM', 'logo' => ['@type' => 'ImageObject', 'url' => RB_SITE . '/icon-512.png']],
  'image' => RB_SITE . '/og.jpg', 'keywords' => implode(', ', $item['tags'] ?? []),
  'articleSection' => $sec[1], 'inLanguage' => ['en', 'ar'], 'isBasedOn' => $item['url'] ?? null,
];
$extra = '<meta name="keywords" content="' . h(implode(', ', array_merge($item['tags'] ?? [], ['BIM', 'AEC', 'AI']))) . '">'
  . '<style>:root{--sec:' . $bg . ';--secInk:#1E2A4A}[data-theme="dark"]{--sec:#20252F;--secInk:#DDE2EA;--solid:' . $ac . '}[data-theme="dark"] .new{background:' . $ac . '}</style>'
  . '<script type="application/ld+json">' . json_encode($ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>';
rb_head($title, $desc, $canon, $extra);
$ext = '<svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 3H3v10h10v-3M9 3h4v4M13 3 7 9"/></svg>';
$li = '<svg viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M3.6 5.9h1.9V13H3.6zM4.55 2.9a1.1 1.1 0 1 1 0 2.2 1.1 1.1 0 0 1 0-2.2zM6.9 5.9h1.8v1h.03c.25-.48.87-.98 1.8-.98 1.92 0 2.27 1.26 2.27 2.9V13h-1.9V9.2c0-.7-.01-1.6-.98-1.6-.98 0-1.13.77-1.13 1.55V13H6.9z"/></svg>';
?>
<nav class="crumbs" aria-label="Breadcrumb"><a href="/posts"><span data-l="ar">كل البوستات</span><span data-l="en">All posts</span></a><span aria-hidden="true">›</span><a class="pill" href="/section/<?= h($c) ?>"><?= rb_icon($c, 14) ?><?= h($sec[2]) ?></a></nav>
<div class="grid">
<main class="main">
 <article class="hero o1">
  <div class="row"><?php if ($isNew): ?><span class="new"><span data-l="ar">جديد</span><span data-l="en">New</span></span><?php endif; ?><span data-l="ar"><?= rb_date($item['date'] ?? '', 'ar') ?></span><span data-l="en"><?= rb_date($item['date'] ?? '', 'en') ?></span></div>
  <h1><?= h($t_en) ?></h1>
  <p class="ar"><?= h($t_ar) ?></p>
  <div class="who"><span class="av" aria-hidden="true"><?= h(rb_initials($au)) ?></span><div><b><?= h($au) ?></b><span data-l="ar">كاتب البوست على LinkedIn</span><span data-l="en">Post author on LinkedIn</span></div></div>
 </article>
 <section class="box o3" aria-labelledby="sum">
  <div class="hd"><h2 id="sum"><span data-l="ar">الفكرة باختصار</span><span data-l="en">In short</span></h2>
   <div class="seg" role="group" aria-label="Text language"><button type="button" data-d="ar" aria-pressed="true">عربي</button><button type="button" data-d="en" aria-pressed="false">English</button></div></div>
  <p class="desc" data-desc="ar" dir="rtl"><?= h($item['desc'] ?? '') ?></p>
  <p class="desc" data-desc="en" dir="ltr" lang="en" hidden><?= h($item['desc_en'] ?? '') ?></p>
  <div class="tags"><?php foreach (($item['tags'] ?? []) as $t): ?><a href="/tag/<?= h(rb_slug($t)) ?>"># <?= h($t) ?></a><?php endforeach; ?></div>
 </section>
 <aside class="note o4"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 11v6M12 7.5v.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/></svg>
  <span data-l="ar">ده ملخص قصير من RadioBIM. البوست الأصلي بكل تفاصيله وصوره عند <?= h($au) ?> على LinkedIn.</span>
  <span data-l="en">This is a short RadioBIM summary. The full post, with all its details and images, is on LinkedIn by <?= h($au) ?>.</span></aside>
<?php
$rel = []; foreach ($all as $i) { if ($i['id'] !== $item['id'] && ($i['c'] ?? '') === $c) { $rel[] = $i; if (count($rel) >= 3) break; } }
if ($rel): ?>
 <section class="o5"><h2 class="sh"><span data-l="ar">بوستات تانية في <?= h($sec[2]) ?></span><span data-l="en">More in <?= h($sec[2]) ?></span></h2>
  <div class="rel"><?php foreach ($rel as $r): ?><a class="ri" href="/p/<?= h($r['id']) ?>"><span class="ic"><?= rb_icon($c) ?></span><span class="tx"><b><?= h(rb_t($r)) ?></b><small><?= h(rb_author($r)) ?></small></span><svg class="ar2" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg></a><?php endforeach; ?></div>
 </section>
<?php endif; ?>
</main>
<aside class="side">
 <div class="panel">
  <?php if (!empty($item['link'])): ?><a class="cta o2" href="<?= h($item['link']) ?>" target="_blank" rel="nofollow noopener"><?= $ext ?><span data-l="ar">افتح الأداة</span><span data-l="en">Open the tool</span> · <?= h($item['linkLabel'] ?? 'Link') ?></a><?php endif; ?>
  <div class="acts o2">
   <a class="ib grow" href="<?= h($item['url']) ?>" target="_blank" rel="noopener"><?= $li ?><span data-l="ar">اقرا البوست الأصلي</span><span data-l="en">Read the original post</span></a>
   <button class="ib sq" type="button" id="share" aria-label="Share"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" aria-hidden="true"><path d="M4 12l16-8-6 16-3-7z"/></svg></button>
   <button class="ib sq" type="button" id="save" aria-pressed="false" aria-label="Save"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" aria-hidden="true"><path d="M6 4h12v16l-6-4-6 4z"/></svg></button>
  </div>
 </div>
 <div class="panel about">
  <span style="font-size:13px;font-weight:700;color:var(--muted)"><span data-l="ar">صاحب البوست</span><span data-l="en">Posted by</span></span>
  <div class="who" style="display:flex"><span class="av" style="background:var(--sec);color:var(--secInk);width:46px;height:46px;font-size:17px"><?= h(rb_initials($au)) ?></span><div><b style="font-size:17px"><?= h($au) ?></b><span style="color:var(--muted)">LinkedIn</span></div></div>
  <dl><dt><span data-l="ar">القسم</span><span data-l="en">Section</span></dt><dd><?= h($sec[2]) ?></dd>
   <dt><span data-l="ar">التاريخ</span><span data-l="en">Date</span></dt><dd><span data-l="ar"><?= rb_date($item['date'] ?? '', 'ar') ?></span><span data-l="en"><?= rb_date($item['date'] ?? '', 'en') ?></span></dd>
   <?php if (!empty($item['linkLabel'])): ?><dt><span data-l="ar">الأداة على</span><span data-l="en">Tool on</span></dt><dd><?= h($item['linkLabel']) ?></dd><?php endif; ?></dl>
 </div>
</aside>
</div>
<script>
(function(){var id=<?= json_encode($item['id']) ?>,K='rbSaved',g=function(){try{return JSON.parse(localStorage.getItem(K)||'[]')}catch(e){return[]}};
var sb=document.getElementById('save');function paint(){sb.setAttribute('aria-pressed',g().indexOf(id)>-1)}paint();
sb.onclick=function(){var a=g(),i=a.indexOf(id);i>-1?a.splice(i,1):a.push(id);try{localStorage.setItem(K,JSON.stringify(a))}catch(e){}paint();};
document.getElementById('share').onclick=function(){var u=location.href.split('#')[0];if(navigator.share){navigator.share({title:document.title,url:u}).catch(function(){})}else if(navigator.clipboard){navigator.clipboard.writeText(u)}};
window.rbSetDesc=function(l){document.querySelectorAll('[data-desc]').forEach(function(p){p.hidden=p.dataset.desc!==l});document.querySelectorAll('[data-d]').forEach(function(b){b.setAttribute('aria-pressed',b.dataset.d===l)})};
document.querySelectorAll('[data-d]').forEach(function(b){b.onclick=function(){rbSetDesc(b.dataset.d)}});
rbSetDesc(document.documentElement.lang==='en'?'en':'ar');
})();
</script>
<?php rb_foot();
