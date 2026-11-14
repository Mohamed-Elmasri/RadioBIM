<?php
// Shared helpers for the server-rendered RadioBIM pages (posts, post, sitemap).
const RB_SITE = 'https://radiobim.com';
const RB_DATA = 'https://raw.githubusercontent.com/Mohamed-Elmasri/RadioBIM/main/data.json';

function rb_data() {
  static $d = null; if ($d !== null) return $d;
  $cache = __DIR__ . '/data-cache.json'; $ttl = 1800;
  if (is_file($cache) && time() - filemtime($cache) < $ttl) {
    $j = json_decode(file_get_contents($cache), true);
    if (!empty($j['items'])) return $d = $j;
  }
  $raw = false;
  if (function_exists('curl_init')) {
    $c = curl_init(RB_DATA);
    curl_setopt_array($c, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 8, CURLOPT_FOLLOWLOCATION => true, CURLOPT_USERAGENT => 'RadioBIM']);
    $raw = curl_exec($c); $code = curl_getinfo($c, CURLINFO_HTTP_CODE); curl_close($c);
    if ($code != 200) $raw = false;
  }
  if ($raw === false && ini_get('allow_url_fopen')) $raw = @file_get_contents(RB_DATA);
  $j = $raw ? json_decode($raw, true) : null;
  if (!empty($j['items'])) { @file_put_contents($cache, $raw); return $d = $j; }
  foreach ([$cache, __DIR__ . '/data.json'] as $f) {
    if (is_file($f)) { $j = json_decode(file_get_contents($f), true); if (!empty($j['items'])) return $d = $j; }
  }
  return $d = ['items' => [], 'sections' => []];
}
function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function rb_slug($s) { $s = strtolower(trim((string)$s)); $s = preg_replace('/[^a-z0-9]+/', '-', $s); return trim($s, '-'); }
function rb_sections() {
  return [
    'ai' => ['AI في الـ AEC', 'AI in AEC', 'AI'],
    'mcp' => ['MCP و AI Agents', 'MCP and AI agents', 'MCP'],
    'addins' => ['Add-ins و Plugins', 'Add-ins and plugins', 'Add-ins'],
    'opensource' => ['Open Source', 'Open source', 'Open Source'],
    'courses' => ['كورسات AI و BIM', 'AI and BIM courses', 'Courses'],
    'ideas' => ['أفكار', 'Ideas', 'Ideas'],
    'tools' => ['أدوات للمهندسين', 'Tools for engineers', 'Tools'],
  ];
}
function rb_sec_color($c) {
  $bg = ['ai'=>'#DDE9FB','mcp'=>'#E6E0FA','addins'=>'#FBE5D6','opensource'=>'#D9F0E4','courses'=>'#FBEFC9','ideas'=>'#F8DDEA','tools'=>'#E4EDCB'];
  $ac = ['ai'=>'#8FB4E8','mcp'=>'#B3A6E6','addins'=>'#E3A982','opensource'=>'#86CDB0','courses'=>'#DCC577','ideas'=>'#E39BB6','tools'=>'#B5CB85'];
  return [$bg[$c] ?? '#EEF1F8', $ac[$c] ?? '#B9C2D3'];
}
function rb_icon($c, $sz = 18) {
  $p = ['ai'=>'M12 3l2 6 6 2-6 2-2 6-2-6-6-2 6-2z','mcp'=>'M8.5 12a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0zM20.5 6a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0zM20.5 18a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0zM8.2 11l7.6-4M8.2 13l7.6 4','addins'=>'M4 4h16v16H4zM12 8v8M8 12h8','opensource'=>'M9 7l-5 5 5 5M15 7l5 5-5 5','courses'=>'M3 9l9-4 9 4-9 4zM7 11v5c3 2 7 2 10 0v-5','ideas'=>'M9 18h6M10 21h4M12 3a6 6 0 0 0-4 10.5V15h8v-1.5A6 6 0 0 0 12 3z','tools'=>'M4 5h16v14H4zM4 10h16M10 5v14'];
  return '<svg aria-hidden="true" width="'.$sz.'" height="'.$sz.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linejoin="round" stroke-linecap="round"><path d="'.($p[$c] ?? 'M4 4h7v7H4zM13 4h7v7h-7zM4 13h7v7H4zM13 13h7v7h-7z').'"/></svg>';
}
function rb_items() {
  $it = rb_data()['items'] ?? [];
  usort($it, function ($a, $b) { return strcmp($b['date'] ?? '', $a['date'] ?? ''); });
  return $it;
}
function rb_t($i) { return $i['title_en'] ?? $i['title'] ?? ''; }
function rb_author($i) { return trim($i['a'] ?? '', " ."); }
function rb_initials($n) { $w = preg_split('/\s+/u', trim($n)); $s = ''; foreach (array_slice($w, 0, 2) as $x) $s .= mb_strtoupper(mb_substr($x, 0, 1)); return $s ?: 'R'; }
function rb_date($d, $l) {
  if (!preg_match('/^(\d{4})-(\d\d)-(\d\d)$/', (string)$d, $m)) return h($d);
  $en = ['January','February','March','April','May','June','July','August','September','October','November','December'];
  $ar = ['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];
  return (int)$m[3] . ' ' . ($l === 'ar' ? $ar[(int)$m[2]-1] : $en[(int)$m[2]-1]) . ' ' . $m[1];
}
function rb_all_tags() {
  $t = [];
  foreach (rb_items() as $i) foreach (($i['tags'] ?? []) as $x) { $s = rb_slug($x); if ($s !== '') { $t[$s]['name'] = $x; $t[$s]['n'] = ($t[$s]['n'] ?? 0) + 1; } }
  uasort($t, function ($a, $b) { return $b['n'] - $a['n']; });
  return $t;
}
function rb_head($title, $desc, $canon, $extra = '') { ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl" data-theme="light">
<head><meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= h($title) ?></title>
<meta name="description" content="<?= h($desc) ?>">
<link rel="canonical" href="<?= h($canon) ?>">
<?php if (strpos($extra, 'name="robots"') === false): ?><meta name="robots" content="index, follow, max-image-preview:large"><?php endif; ?>
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="icon" href="/favicon-32.png" sizes="32x32" type="image/png">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">
<link rel="manifest" href="/manifest.webmanifest">
<meta name="theme-color" content="#1E2A4A">
<meta property="og:type" content="article"><meta property="og:site_name" content="RadioBIM">
<meta property="og:title" content="<?= h($title) ?>"><meta property="og:description" content="<?= h($desc) ?>">
<meta property="og:url" content="<?= h($canon) ?>"><meta property="og:image" content="<?= RB_SITE ?>/og.jpg">
<meta name="twitter:card" content="summary_large_image">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Baloo+Bhaijaan+2:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<script>(function(){var H=document.documentElement,g=function(k){try{return localStorage.getItem(k)}catch(e){return null}};var l=g('rbLang')||((navigator.language||'').slice(0,2)==='ar'?'ar':'en');if(l!=='ar'&&l!=='en')l='ar';var t=g('rbTheme')||(matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light');H.lang=l;H.dir=l==='ar'?'rtl':'ltr';H.dataset.theme=t;})();</script>
<style>
:root{--bg:#F5F7FB;--card:#FFFFFF;--fg:#1E2A4A;--muted:#5E6A85;--brand:#3B4E86;--chip:#E9EDF5;--line:#E2E7F1;--note:#EEF1F8;--solid:#1E2A4A;--onSolid:#FFFFFF;--secMuted:#5B4A3E}
[data-theme="dark"]{--bg:#161A22;--card:#1E232D;--fg:#DDE2EA;--muted:#9AA3B2;--brand:#8FB4E8;--chip:#262C38;--line:#2C3340;--note:#1E232D;--onSolid:#161A22;--secMuted:#B9A99C}
*{box-sizing:border-box}html{-webkit-text-size-adjust:100%}
body{margin:0;background:var(--bg);color:var(--fg);font-family:'Baloo Bhaijaan 2','Segoe UI',Tahoma,sans-serif;font-size:16px;line-height:1.6}
a{color:var(--brand)}
.muted{color:var(--muted)}
.wrap{max-width:1120px;margin:0 auto;padding:0 16px 40px;display:flex;flex-direction:column;gap:14px}
header{direction:ltr;padding:16px 0 2px;display:flex;align-items:center;gap:6px}
.brand{margin-right:auto;white-space:nowrap;color:var(--fg);text-decoration:none;font-size:21px;font-weight:800;line-height:1.2}
.brand svg{height:.8em;width:.914em;vertical-align:-.04em;margin-right:.28em;color:var(--brand)}.brand i{font-style:normal;color:var(--brand)}.brand sup{font-size:.42em;color:var(--muted)}
.ib{height:40px;min-width:40px;padding:0;border-radius:13px;border:0;background:var(--chip);color:var(--fg);display:inline-flex;align-items:center;justify-content:center;gap:8px;font:inherit;font-weight:700;font-size:14px;text-decoration:none;cursor:pointer}
.ib svg{width:20px;height:20px}.ib.txt{padding:0 14px;white-space:nowrap}
.crumbs{display:flex;align-items:center;gap:6px;font-size:13px;font-weight:700;color:var(--muted)}
.crumbs a{color:var(--muted);text-decoration:none}
.pill{display:inline-flex;align-items:center;gap:5px;padding:2px 10px 2px 8px;border-radius:999px;background:var(--sec);color:var(--secInk)!important}
.hero{background:var(--sec);color:var(--secInk);border-radius:26px;padding:20px 18px 18px;display:flex;flex-direction:column;gap:10px}
.hero .row{display:flex;align-items:center;gap:8px;font-size:13px;color:var(--secMuted)}
.new{font-size:12px;font-weight:800;padding:1px 9px;border-radius:8px;background:var(--solid);color:var(--onSolid)}
.hero h1{margin:0;font-size:27px;line-height:1.2;font-weight:800;direction:ltr;text-align:left}
.hero .ar{margin:0;font-size:16px;font-weight:600;color:var(--secMuted);direction:rtl;text-align:right}
.who{display:flex;align-items:center;gap:10px;margin-top:4px}
.av{width:36px;height:36px;border-radius:50%;background:var(--card);color:var(--fg);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:14px;flex:none}
.who b{display:block;font-size:15px;line-height:1.25}.who span{font-size:12.5px;color:var(--secMuted)}
.cta{height:54px;border-radius:18px;background:var(--solid);color:var(--onSolid)!important;display:flex;align-items:center;justify-content:center;gap:10px;font-size:17px;font-weight:800;text-decoration:none}
.cta svg{width:20px;height:20px}
.acts{display:flex;gap:8px}.acts .grow{flex:1;height:48px;border-radius:16px}.acts .sq{width:48px;height:48px;border-radius:16px}
.ib[aria-pressed="true"]{background:var(--solid);color:var(--onSolid)}.ib[aria-pressed="true"] svg path{fill:currentColor}
.box{background:var(--card);border:1px solid var(--line);border-radius:22px;padding:16px 16px 14px;display:flex;flex-direction:column;gap:10px}
.box .hd{display:flex;align-items:center;gap:8px}.box h2{margin:0;font-size:17px;margin-inline-end:auto}
.seg{direction:ltr;display:flex;padding:3px;border-radius:11px;background:var(--chip)}
.seg button{height:28px;padding:0 10px;border-radius:8px;border:0;font:inherit;font-size:13px;font-weight:700;background:transparent;color:var(--fg);cursor:pointer}
.seg button[aria-pressed="true"]{background:var(--card)}
.desc{margin:0;font-size:16.5px;line-height:1.7}.desc[hidden]{display:none}
.tags{direction:ltr;display:flex;flex-wrap:wrap;gap:6px}html[dir="rtl"] .tags{justify-content:flex-end}
.tags a{font-size:12.5px;font-weight:700;padding:3px 10px;border-radius:9px;background:var(--chip);color:var(--fg);text-decoration:none}
.note{display:flex;gap:10px;align-items:flex-start;padding:12px 14px;border-radius:18px;background:var(--note);font-size:13.5px;line-height:1.6}
.note svg{flex:none;margin-top:2px}
h2.sh{margin:6px 0 2px;font-size:18px}
.rel{display:flex;flex-direction:column;gap:8px}
.ri{display:flex;align-items:center;gap:12px;padding:12px 14px;border-radius:18px;background:var(--card);border:1px solid var(--line);color:var(--fg);text-decoration:none}
.ri .ic{flex:none;width:38px;height:38px;border-radius:12px;background:var(--sec);color:var(--secInk);display:flex;align-items:center;justify-content:center}
.ri .tx{flex:1;min-width:0;display:flex;flex-direction:column;line-height:1.3}.ri b{font-size:15px;direction:ltr;text-align:left}.ri small{font-size:12.5px;color:var(--muted)}
.ri .ar2{flex:none;color:var(--muted)}html[dir="rtl"] .ri .ar2{transform:scaleX(-1)}
.side{display:flex;flex-direction:column;gap:12px}.side .about{display:none}
.about dl{margin:0;display:grid;grid-template-columns:auto 1fr;gap:6px 14px;font-size:14px}.about dt{color:var(--muted)}.about dd{margin:0;font-weight:700}
footer{margin-top:8px;display:flex;flex-wrap:wrap;justify-content:center;gap:6px 14px;font-size:13px;color:var(--muted)}footer a{font-weight:700;text-decoration:none}
[data-l]{display:none}html[lang="ar"] [data-l="ar"],html[lang="en"] [data-l="en"]{display:revert}
.card{background:var(--card);border:1px solid var(--line);border-radius:18px;padding:14px 16px}
.card h3{margin:0;font-size:17px;line-height:1.35;direction:ltr;text-align:left}.card h3 a{color:var(--fg);text-decoration:none}.card p{margin:4px 0 0;color:var(--muted)}
.list{display:grid;grid-template-columns:repeat(auto-fill,minmax(min(100%,330px),1fr));gap:10px}
.nav{display:flex;flex-wrap:wrap;gap:6px}
.search input{width:100%;height:46px;border-radius:14px;border:1px solid var(--line);background:var(--card);color:var(--fg);padding:0 14px;font:inherit}
@media (min-width:900px){
 .wrap{padding:0 24px 40px;gap:18px}.brand{font-size:26px}.ib{height:42px;min-width:42px;border-radius:14px}
 .grid{display:grid;grid-template-columns:minmax(0,1fr) 340px;gap:24px;align-items:start}
 .main{display:flex;flex-direction:column;gap:16px;min-width:0}
 .hero{border-radius:30px;padding:32px 32px 28px;gap:12px}.hero h1{font-size:42px;line-height:1.15}.hero .ar{font-size:20px}.hero .who{display:none}
 .box{border-radius:24px;padding:22px 24px}.box h2{font-size:20px}.desc{font-size:19px;line-height:1.75;max-width:64ch}
 .rel{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px}.ri{flex-direction:column;align-items:flex-start;gap:8px;padding:16px;border-radius:20px}.ri .ar2{display:none}
 .side{position:sticky;top:16px}.side .about{display:flex}
 .side .panel{background:var(--card);border:1px solid var(--line);border-radius:24px;padding:18px;display:flex;flex-direction:column;gap:10px}
 .side .acts{flex-wrap:wrap}.side .acts .grow{flex:1 1 100%}.side .acts .sq{flex:1;width:auto}
}
@media (max-width:899px){.grid{display:flex;flex-direction:column;gap:14px}.main,.side,.side .panel{display:contents}.o1{order:1}.o2{order:2}.o3{order:3}.o4{order:4}.o5{order:5}.o6{order:6}.side .about{display:none}}
@media (max-width:420px){.ib.txt{display:none}}
</style>
<?= $extra ?>
</head>
<body><div class="wrap">
<header>
 <a class="brand" href="/" aria-label="RadioBIM"><svg viewBox="4 8 56 49" aria-hidden="true"><g fill="none" stroke="currentColor" stroke-width="4.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21 44 11"/><rect x="7" y="21" width="50" height="33" rx="7"/></g><path fill="currentColor" d="M23.5527950310559 37.130434782608695Q24.73540372670807 37.531677018633545 25.36894409937888 38.27080745341615Q25.98136645962733 39.03105590062112 25.98136645962733 40.150310559006215Q25.98136645962733 42.219875776397515 24.41863354037267 43.23354037267081Q22.85590062111801 44.247204968944104 19.962732919254655 44.247204968944104Q19.47701863354037 44.247204968944104 18.80124223602484 44.20496894409938Q18.125465838509314 44.16273291925466 17.428571428571427 44.04658385093168Q16.73167701863354 43.9304347826087 16.161490683229815 43.7192546583851Q14.999999999999998 43.27577639751553 14.999999999999998 42.177639751552796V32.31552795031056Q14.999999999999998 31.872049689440992 15.253416149068322 31.62919254658385Q15.506832298136645 31.386335403726708 15.90807453416149 31.238509316770187Q16.62608695652174 30.985093167701862 17.71366459627329 30.868944099378883Q18.801242236024844 30.752795031055904 19.920496894409936 30.752795031055904Q22.60248447204969 30.752795031055904 24.070186335403726 31.671428571428574Q25.537888198757763 32.590062111801245 25.537888198757763 34.469565217391306Q25.537888198757763 35.52546583850932 24.904347826086955 36.2223602484472Q24.39751552795031 36.792546583850935 23.5527950310559 37.130434782608695ZM20.321739130434782 38.69316770186335H18.59006211180124V41.2695652173913Q18.885714285714286 41.35403726708075 19.360869565217392 41.385714285714286Q19.836024844720498 41.41739130434783 20.23726708074534 41.41739130434783Q21.08198757763975 41.41739130434783 21.6416149068323 41.06894409937888Q22.201242236024846 40.72049689440994 22.201242236024846 39.98136645962733Q22.201242236024846 39.28447204968944 21.72608695652174 38.9888198757764Q21.250931677018635 38.69316770186335 20.321739130434782 38.69316770186335ZM20.13167701863354 36.1167701863354Q21.039751552795032 36.1167701863354 21.451552795031056 35.778881987577634Q21.86335403726708 35.440993788819874 21.86335403726708 34.78633540372671Q21.86335403726708 34.216149068322984 21.388198757763973 33.878260869565224Q20.913043478260867 33.54037267080746 19.962732919254655 33.54037267080746Q19.645962732919255 33.54037267080746 19.234161490683228 33.56149068322982Q18.822360248447204 33.582608695652176 18.59006211180124 33.624844720496895V36.1167701863354ZM28.072049689440995 34.57515527950311V31.069565217391304Q28.325465838509317 31.027329192546585 28.821739130434782 30.953416149068325Q29.318012422360248 30.87950310559006 29.782608695652172 30.87950310559006Q30.838509316770185 30.87950310559006 31.30310559006211 31.238509316770184Q31.767701863354038 31.59751552795031 31.767701863354038 32.632298136645964V43.88819875776397Q31.535403726708076 43.951552795031056 31.049689440993788 44.01490683229814Q30.563975155279504 44.07826086956522 30.057142857142857 44.07826086956522Q28.959006211180125 44.07826086956522 28.51552795031056 43.68757763975155Q28.072049689440995 43.29689440993789 28.072049689440995 42.32546583850932ZM43.36149068322981 40.76273291925466Q43.086956521739125 40.97391304347826 42.65403726708074 41.12173913043478Q42.22111801242236 41.2695652173913 41.65093167701863 41.2695652173913Q40.89068322981366 41.2695652173913 40.3944099378882 41.079503105590064Q39.89813664596273 40.889440993788824 39.75031055900621 40.42484472049689Q39.15900621118012 38.54534161490683 38.778881987577634 37.40496894409938Q38.398757763975155 36.26459627329193 38.18757763975155 35.398757763975155H38.08198757763975Q38.01863354037267 36.64472049689441 37.98695652173913 37.668944099378876Q37.95527950310559 38.69316770186335 37.944720496894405 39.654037267080746Q37.93416149068323 40.61490683229814 37.90248447204969 41.61801242236025Q37.87080745341615 42.621118012422365 37.807453416149066 43.8248447204969Q37.53291925465838 43.90931677018634 37.08944099378881 43.993788819875775Q36.64596273291925 44.07826086956522 36.20248447204969 44.07826086956522Q35.2944099378882 44.07826086956522 34.76645962732919 43.78260869565217Q34.238509316770184 43.48695652173913 34.238509316770184 42.7055900621118Q34.238509316770184 42.177639751552796 34.2807453416149 41.25900621118012Q34.32298136645962 40.340372670807454 34.396894409937886 39.18944099378882Q34.47080745341615 38.03850931677019 34.54472049689441 36.77142857142857Q34.61863354037267 35.504347826086956 34.70310559006211 34.2583850931677Q34.787577639751554 33.01242236024845 34.85093167701863 31.935403726708074Q35.01987577639751 31.66086956521739 35.59006211180124 31.344099378881985Q36.160248447204964 31.027329192546585 37.19503105590062 31.027329192546585Q38.31428571428571 31.027329192546585 38.9583850931677 31.37577639751553Q39.60248447204969 31.72422360248447 39.877018633540374 32.5055900621118Q40.130434782608695 33.18136645962733 40.42608695652174 34.1Q40.721739130434784 35.018633540372676 41.017391304347825 35.958385093167706Q41.313043478260866 36.89813664596274 41.54534161490683 37.637267080745346H41.65093167701863Q42.15776397515528 35.88447204968944 42.6751552795031 34.2055900621118Q43.19254658385093 32.52670807453416 43.593788819875776 31.42857142857143Q43.91055900621118 31.259627329192547 44.41739130434783 31.143478260869564Q44.924223602484474 31.027329192546585 45.515527950310556 31.027329192546585Q46.550310559006206 31.027329192546585 47.20496894409938 31.33354037267081Q47.85962732919255 31.639751552795033 47.96521739130435 32.20993788819876Q48.04968944099379 32.632298136645964 48.14472049689441 33.49813664596273Q48.23975155279503 34.363975155279505 48.34534161490683 35.48322981366459Q48.45093167701863 36.60248447204969 48.55652173913043 37.816770186335404Q48.66211180124223 39.03105590062112 48.75714285714285 40.17142857142858Q48.85217391304348 41.31180124223603 48.91552795031056 42.2304347826087Q48.97888198757764 43.149068322981364 49.0 43.67701863354037Q48.64099378881988 43.88819875776397 48.25031055900621 43.98322981366459Q47.85962732919255 44.07826086956522 47.2472049689441 44.07826086956522Q46.46583850931677 44.07826086956522 45.906211180124224 43.803726708074535Q45.34658385093168 43.52919254658385 45.30434782608695 42.726708074534166Q45.219875776397515 41.185093167701865 45.177639751552796 39.854658385093174Q45.13540372670808 38.524223602484476 45.114285714285714 37.42608695652174Q45.09316770186335 36.327950310559004 45.05093167701863 35.504347826086956H44.94534161490683Q44.73416149068323 36.327950310559004 44.33291925465838 37.57391304347826Q43.931677018633536 38.81987577639752 43.36149068322981 40.76273291925466Z"/></svg>Radio<i>BIM</i><sup>®</sup></a>
 <a class="ib txt" href="/posts"><span data-l="ar">كل البوستات</span><span data-l="en">All posts</span></a>
 <a class="ib" href="/posts" aria-label="Search"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 11a7 7 0 1 1-14 0 7 7 0 0 1 14 0zM20 20l-3.5-3.5"/></svg></a>
 <button class="ib" type="button" id="rbLangBtn" aria-label="Language"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18zM3 12h18M12 3c3 3 3 15 0 18M12 3c-3 3-3 15 0 18"/></svg></button>
 <button class="ib" type="button" id="rbThemeBtn" aria-label="Theme"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path id="rbThemeIc" d="M20 14.5A8 8 0 0 1 9.5 4a8 8 0 1 0 10.5 10.5z"/></svg></button>
</header>
<?php }
function rb_foot() { ?>
<footer><a href="/posts"><span data-l="ar">كل البوستات</span><span data-l="en">All posts</span></a><a href="/guide.html"><span data-l="ar">الدليل</span><span data-l="en">Guide</span></a><span>RadioBIM® · radiobim.com</span></footer>
</div>
<script>
(function(){var H=document.documentElement,S=function(k,v){try{localStorage.setItem(k,v)}catch(e){}};
var sun='M12 16a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM12 2v2M12 20v2M2 12h2M20 12h2M5 5l1.4 1.4M17.6 17.6L19 19M5 19l1.4-1.4M17.6 6.4L19 5',moon='M20 14.5A8 8 0 0 1 9.5 4a8 8 0 1 0 10.5 10.5z';
function ic(){var e=document.getElementById('rbThemeIc');if(e)e.setAttribute('d',H.dataset.theme==='dark'?sun:moon);}ic();
document.getElementById('rbThemeBtn').onclick=function(){H.dataset.theme=H.dataset.theme==='dark'?'light':'dark';S('rbTheme',H.dataset.theme);ic();};
document.getElementById('rbLangBtn').onclick=function(){var l=H.lang==='ar'?'en':'ar';H.lang=l;H.dir=l==='ar'?'rtl':'ltr';S('rbLang',l);if(window.rbSetDesc)rbSetDesc(l);};
})();
</script>
</body></html>
<?php }
function rb_card($i) {
  $id = h($i['id']); ?>
<article class="card">
 <h3><a href="/p/<?= $id ?>"><?= h(rb_t($i)) ?></a></h3>
 <p dir="rtl" style="text-align:right"><?= h($i['desc'] ?? '') ?></p>
 <?php if (!empty($i['desc_en'])): ?><p dir="ltr" lang="en" style="text-align:left"><?= h($i['desc_en']) ?></p><?php endif; ?>
 <div class="tags" style="margin-top:8px"><?php foreach (($i['tags'] ?? []) as $t): ?><a href="/tag/<?= h(rb_slug($t)) ?>"><?= h($t) ?></a><?php endforeach; ?></div>
</article>
<?php }
