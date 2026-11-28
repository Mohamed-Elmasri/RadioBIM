<?php
// Private stats page: radiobim.com/stats.php?k=KEY (only the sha256 of the key is stored here).
const RB_KEY_SHA = 'f32876d0751aae140b61a59a55c0d41b3ce6d722e91a1024eebb2bf6c2fbe03e';
header('X-Robots-Tag: noindex, nofollow'); header('Cache-Control: no-store');
if (!hash_equals(RB_KEY_SHA, hash('sha256', (string)($_GET['k'] ?? '')))) { http_response_code(404); exit('Not found'); }
require __DIR__ . '/_rb.php';
date_default_timezone_set('Africa/Cairo');
$dir = dirname(__DIR__) . '/radiobim-stats';
$days = max(1, min(90, (int)($_GET['d'] ?? 30)));
$all = [];
for ($i = 0; $i < 4; $i++) { $f = $dir . '/' . date('Y-m', strtotime("first day of -$i month")) . '.json'; if (is_file($f)) $all += (json_decode(file_get_contents($f), true) ?: []);
  $fe = substr($f, 0, -5) . '-est.json'; if (is_file($fe)) foreach ((json_decode(file_get_contents($fe), true) ?: []) as $k => $e) {
    $d = ($all[$k] ?? []) + ['ev' => [], 'u' => [], 'id' => [], 'x' => []];
    foreach ($e['ev'] ?? [] as $x => $n) $d['ev'][$x] = ($d['ev'][$x] ?? 0) + $n;
    foreach ($e['id'] ?? [] as $x => $m) foreach ($m as $id => $n) $d['id'][$x][$id] = ($d['id'][$x][$id] ?? 0) + $n;
    $d['uc'] = ($d['uc'] ?? 0) + ($e['uc'] ?? 0); $d['est'] = 1; $all[$k] = $d; } }
$from = date('Y-m-d', strtotime('-' . ($days - 1) . ' days'));
$rows = []; $uc = 0; $T = ['u' => 0, 'ev' => [], 'id' => [], 'x' => []]; $uAll = [];
for ($t = strtotime($from); $t <= time(); $t += 86400) {
  $k = date('Y-m-d', $t); $d = $all[$k] ?? ['ev' => [], 'u' => [], 'id' => [], 'x' => []];
  $rows[$k] = $d;
  foreach ($d['ev'] as $e => $n) $T['ev'][$e] = ($T['ev'][$e] ?? 0) + $n;
  foreach ($d['u'] as $v => $_) $uAll[$v] = 1; $uc += $d['uc'] ?? 0;
  foreach ($d['id'] as $e => $m) foreach ($m as $id => $n) $T['id'][$e][$id] = ($T['id'][$e][$id] ?? 0) + $n;
  foreach ($d['x'] as $e => $m) foreach ($m as $v => $n) $T['x'][$e][$v] = ($T['x'][$e][$v] ?? 0) + $n;
}
$items = []; foreach (rb_items() as $it) $items[$it['id']] = $it;
$h = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
$app = $T['x']['view.s']['1'] ?? 0;
function top($m, $n = 12) { arsort($m); return array_slice($m, 0, $n, true); }
$score = [];
foreach (['pview' => 1, 'open' => 1, 'tool' => 2, 'li' => 2, 'share' => 3, 'save' => 3] as $e => $w) foreach ($T['id'][$e] ?? [] as $id => $n) $score[$id] = ($score[$id] ?? 0) + $n * $w;
?><!doctype html><html lang="ar" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>RadioBIM — الإحصائيات</title>
<style>body{font-family:system-ui,Segoe UI,Tahoma,sans-serif;margin:0;background:#F6F7FB;color:#1E2A4A}main{max-width:980px;margin:0 auto;padding:20px 16px 40px}h1{font-size:22px;margin:0 0 4px}h2{font-size:16px;margin:26px 0 10px}.sub{color:#4A5677;font-size:13px}.kpis{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:10px;margin-top:16px}.k{background:#fff;border-radius:14px;padding:12px 14px;border:1px solid #E3E7F1}.k b{display:block;font-size:24px}.k span{font-size:12.5px;color:#4A5677}table{width:100%;border-collapse:collapse;background:#fff;border-radius:12px;overflow:hidden;border:1px solid #E3E7F1;font-size:13.5px}th,td{padding:7px 10px;text-align:start;border-bottom:1px solid #EEF1F8}th{background:#EEF1F8;font-weight:600}td.n{text-align:center;font-variant-numeric:tabular-nums}.bar{display:inline-block;height:8px;border-radius:4px;background:#3B4E86;vertical-align:middle}.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:14px}a{color:#3B4E86}.sel a{margin-inline-end:10px}.es{color:#8A94AE;font-weight:700}.pl{margin:0;padding-inline-start:22px;font-size:14px}.pl li{padding:5px 0;border-bottom:1px solid #EEF1F8}.pl b{margin-inline-start:10px;color:#4A5677;font-variant-numeric:tabular-nums}.pl a{text-decoration:none}</style></head><body><main>
<h1>إحصائيات RadioBIM</h1><div class="sub">زوار حقيقيين بس (الـ bots مش بتتحسب). مفيش أي بيانات شخصية بتتسجل. · <span class="sel">آخر: <a href="?k=<?= $h($_GET['k']) ?>&d=7">7 أيام</a><a href="?k=<?= $h($_GET['k']) ?>&d=30">30 يوم</a><a href="?k=<?= $h($_GET['k']) ?>&d=90">90 يوم</a></span> (من <?= $from ?>)</div>
<div class="kpis">
<div class="k"><b><?= count($uAll) + $uc ?></b><span>زائر (جهاز) مختلف</span></div>
<div class="k"><b><?= $T['ev']['view'] ?? 0 ?></b><span>زيارة للصفحة الرئيسية</span></div>
<div class="k"><b><?= $app ?></b><span>فتح من التطبيق المتثبت</span></div>
<div class="k"><b><?= ($T['ev']['install'] ?? 0) ?></b><span>تثبيت للتطبيق</span></div>
<div class="k"><b><?= $T['ev']['open'] ?? 0 ?></b><span>فتح تفاصيل بوست</span></div>
<div class="k"><b><?= $T['ev']['tool'] ?? 0 ?></b><span>ضغط على لينك أداة</span></div>
<div class="k"><b><?= $T['ev']['li'] ?? 0 ?></b><span>فتح بوست على LinkedIn</span></div>
<div class="k"><b><?= ($T['ev']['share'] ?? 0) + ($T['ev']['save'] ?? 0) ?></b><span>مشاركة + حفظ</span></div>
<div class="k"><b><?= ($T['ev']['pview'] ?? 0) ?></b><span>فتح صفحة بوست (/p/)</span></div>
<div class="k"><b><?= ($T['ev']['guide'] ?? 0) ?></b><span>فتح الدليل</span></div>
</div>
<h2>يوم بيوم</h2>
<table><tr><th>اليوم</th><th>زوار</th><th>زيارات</th><th>من التطبيق</th><th>فتح بوست</th><th>أدوات</th><th>LinkedIn</th><th>صفحات /p/</th></tr>
<?php $mx = 1; foreach ($rows as $d) $mx = max($mx, count($d['u']) + ($d['uc'] ?? 0)); foreach (array_reverse($rows, true) as $k => $d): $u = count($d['u']) + ($d['uc'] ?? 0); $es = !empty($d['est']); ?>
<tr><td><?= $k ?><?= $es ? ' <span class="es" title="تقديري من HostGator">~</span>' : '' ?></td><td class="n"><span class="bar" style="width:<?= round($u / $mx * 60) ?>px"></span> <?= $u ?></td><td class="n"><?= $d['ev']['view'] ?? 0 ?></td><td class="n"><?= $d['x']['view.s']['1'] ?? 0 ?></td><td class="n"><?= $d['ev']['open'] ?? 0 ?></td><td class="n"><?= $d['ev']['tool'] ?? 0 ?></td><td class="n"><?= $d['ev']['li'] ?? 0 ?></td><td class="n"><?= $d['ev']['pview'] ?? 0 ?></td></tr>
<?php endforeach; ?></table>
<h2>أكتر البوستات تفاعلاً</h2>
<?php $tot = []; foreach (['pview','open','tool','li','share','save'] as $e) foreach ($T['id'][$e] ?? [] as $id => $n) $tot[$id] = ($tot[$id] ?? 0) + $n; ?>
<table><tr><th style="width:36px">#</th><th>البوست</th><th style="width:110px">إجمالي الزيارات</th></tr><?php $i = 0; foreach (top($tot, 20) as $id => $n): $it = $items[$id] ?? null; ?><tr><td class="n"><?= ++$i ?></td><td><?php if ($it): ?><a href="/p/<?= $h($id) ?>" target="_blank"><?= $h($it['title']) ?></a><?php else: ?><?= $h($id) ?><?php endif; ?></td><td class="n"><?= $n ?></td></tr><?php endforeach; ?></table>
<div class="grid">
<?php
$L = ['view.r' => 'جايين منين', 'view.m' => 'الجهاز', 'view.l' => 'اللغة', 'tool.t' => 'نوع لينك الأداة اللي اتضغط', 'filter.f' => 'الفلاتر المستخدمة', 'search.q' => 'أكتر كلمات البحث'];
$NM = ['view.m' => ['1' => 'موبايل', '0' => 'كمبيوتر'], 'view.r' => ['-' => 'مباشر / تطبيق LinkedIn']];
foreach ($L as $k => $title): $m = top($T['x'][$k] ?? [], 12); if (!$m) continue; ?>
<div><h2><?= $title ?></h2><table><?php foreach ($m as $v => $n): ?><tr><td><?= $h($NM[$k][$v] ?? $v) ?></td><td class="n"><?= $n ?></td></tr><?php endforeach; ?></table></div>
<?php endforeach; ?>
</div>
<p class="sub" style="margin-top:24px"><span class="es">~</span> الأيام من 12 لـ 28 نوفمبر (قبل تركيب العداد) أرقامها تقديرية من سجلات HostGator: الزوار من عدد المتصفحات الحقيقية (~820)، وصفحات البوستات بعد طرح زحف محركات البحث. فتح البوست والأدوات وLinkedIn بتتعد من يوم 28 بس.</p>
</main></body></html>
