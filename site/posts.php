<?php
require __DIR__ . '/_rb.php';
$S = rb_sections(); $items = rb_items(); $tags = rb_all_tags();
$tag = isset($_GET['tag']) ? rb_slug($_GET['tag']) : '';
$sec = isset($_GET['sec']) && isset($S[$_GET['sec']]) ? $_GET['sec'] : '';
$q = isset($_GET['q']) ? trim(mb_substr((string)$_GET['q'], 0, 80)) : '';
if ($tag !== '' && !isset($tags[$tag])) { http_response_code(404); }
$list = array_values(array_filter($items, function ($i) use ($tag, $sec, $q) {
  if ($sec !== '' && ($i['c'] ?? '') !== $sec) return false;
  if ($tag !== '') { $ok = false; foreach (($i['tags'] ?? []) as $t) if (rb_slug($t) === $tag) $ok = true; if (!$ok) return false; }
  if ($q !== '') { $hay = mb_strtolower(implode(' ', [$i['title'] ?? '', $i['desc'] ?? '', $i['title_en'] ?? '', $i['desc_en'] ?? '', $i['a'] ?? '', implode(' ', $i['tags'] ?? [])])); if (mb_strpos($hay, mb_strtolower($q)) === false) return false; }
  return true;
}));
if ($tag !== '') {
  $name = $tags[$tag]['name'] ?? $tag;
  $title = $name . ' — AI, BIM tools and LinkedIn posts | RadioBIM';
  $desc = 'Latest ' . $name . ' posts, tools, add-ins and ideas for AEC engineers, collected daily from LinkedIn. أحدث بوستات وأدوات ' . $name . ' لمهندسين الـ AEC.';
  $canon = RB_SITE . '/tag/' . $tag; $h1 = $name;
} elseif ($sec !== '') {
  $title = $S[$sec][1] . ' — ' . $S[$sec][0] . ' | RadioBIM';
  $desc = $S[$sec][1] . ' for AEC and BIM engineers, collected daily from LinkedIn. ' . $S[$sec][0] . ' من LinkedIn كل يوم.';
  $canon = RB_SITE . '/section/' . $sec; $h1 = $S[$sec][0] . ' · ' . $S[$sec][1];
} else {
  $title = 'All posts — AI, BIM, Revit add-ins and AEC tools | RadioBIM';
  $desc = 'Every RadioBIM post: AI in AEC, MCP and AI agents, Revit and Navisworks add-ins, open source, courses, ideas and tools, collected daily from LinkedIn.';
  $canon = RB_SITE . '/posts'; $h1 = 'كل البوستات · All posts';
}
$extra = '';
if ($q !== '') { $extra .= '<meta name="robots" content="noindex, follow">'; $h1 = '“' . $q . '”'; }
$ld = ['@context' => 'https://schema.org', '@type' => 'CollectionPage', 'name' => $title, 'url' => $canon, 'description' => $desc,
  'mainEntity' => ['@type' => 'ItemList', 'numberOfItems' => count($list), 'itemListElement' => array_map(function ($i, $k) {
    return ['@type' => 'ListItem', 'position' => $k + 1, 'url' => RB_SITE . '/p/' . $i['id'], 'name' => rb_t($i)];
  }, array_slice($list, 0, 100), array_keys(array_slice($list, 0, 100)))]];
$extra .= '<script type="application/ld+json">' . json_encode($ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>';
rb_head($title, $desc, $canon, $extra);
?>
<h1><?= h($h1) ?></h1>
<p class="muted"><?= count($list) ?> بوست · posts</p>
<form action="/posts" method="get" style="margin:8px 0 12px"><input name="q" value="<?= h($q) ?>" placeholder="دوّر على أداة أو موضوع · Search" style="width:100%;height:42px;border-radius:12px;border:1px solid var(--line);background:var(--card);color:var(--fg);padding:0 14px;font:inherit"></form>
<div class="nav"><a class="btn" href="/posts">الكل</a><?php foreach ($S as $k => $v): ?><a class="btn" href="/section/<?= $k ?>"><?= h($v[0]) ?></a><?php endforeach; ?></div>
<?php foreach ($list as $i) rb_card($i); ?>
<h2>المواضيع · Topics</h2>
<div class="tags"><?php foreach (array_slice($tags, 0, 80, true) as $s => $t): ?><a href="/tag/<?= h($s) ?>"><?= h($t['name']) ?> (<?= $t['n'] ?>)</a><?php endforeach; ?></div>
<?php rb_foot();
