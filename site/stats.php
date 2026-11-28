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
for ($i = 0; $i < 4; $i++) { $f = $dir . '/' . date('Y-m', strtotime("first day of -$i month")) . '.json'; if (is_file($f)) $all += (json_decode(file_get_contents($f), true) ?: []); }
$from = date('Y-m-d', strtotime('-' . ($days - 1) . ' days'));
$rows = []; $T = ['u' => 0, 'ev' => [], 'id' => [], 'x' => []]; $uAll = [];
for ($t = strtotime($from); $t <= time(); $t += 86400) {
  $k = date('Y-m-d', $t); $d = $all[$k] ?? ['ev' => [], 'u' => [], 'id' => [], 'x' => []];
  $rows[$k] = $d;
  foreach ($d['ev'] as $e => $n) $T['ev'][$e] = ($T['ev'][$e] ?? 0) + $n;
  foreach ($d['u'] as $v => $_) $uAll[$v] = 1;
  foreach ($d['id'] as $e => $m) foreach ($m as $id => $n) $T['id'][$e][$id] = ($T['id'][$e][$id] ?? 0) + $n;
  foreach ($d['x'] as $e => $m) foreach ($m as $v => $n) $T['x'][$e][$v] = ($T['x'][$e][$v] ?? 0) + $n;
}
$items = []; foreach (rb_items() as $it) $items[$it['id']] = $it;
$h = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
$app = $T['x']['view.s']['1'] ?? 0;
function top($m, $n = 12) { arsort($m); return array_slice($m, 0, $n, true); }
$score = [];
foreach (['open' => 1, 'tool' => 2, 'li' => 2, 'share' => 3, 'save' => 3] as $e => $w) foreach ($T['id'][$e] ?? [] as $id => $n) $score[$id] = ($score[$id] ?? 0) + $n * $w;
?><!doctype html><html lang="ar" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>RadioBIM — الإحصائيات</title>
<style>body{font-family:system-ui,Segoe UI,Tahoma,sans-serif;margin:0;background:#F6F7FB;color:#1E2A4A}main{max-width:980px;margin:0 auto;padding:20px 16px 40px}h1{font-size:22px;margin:0 0 4px}h2{font-size:16px;margin:26px 0 10px}.sub{color:#4A5677;font-size:13px}.kpis{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:10px;margin-top:16px}.k{background:#fff;border-radius:14px;padding:12px 14px;border:1px solid #E3E7F1}.k b{display:block;font-size:24px}.k span{font-size:12.5px;color:#4A5677}table{width:100%;border-collapse:collapse;background:#fff;border-radius:12px;overflow:hidden;border:1px solid #E3E7F1;font-size:13.5px}th,td{padding:7px 10px;text-align:start;border-bottom:1px solid #EEF1F8}th{background:#EEF1F8;font-weight:600}td.n{text-align:center;font-variant-numeric:tabular-nums}.bar{display:inline-block;height:8px;border-radius:4px;background:#3B4E86;vertical-align:middle}.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:14px}a{color:#3B4E86}.sel a{margin-inline-end:10px}</style></head><body><main>
<h1>إحصائيات RadioBIM</h1><div class="sub">زوار حقيقيين بس (الـ bots مش بتتحسب). مفيش أي بيانات شخصية بتتسجل. · <span class="sel">آخر: <a href="?k=<?= $h($_GET['k']) ?>&d=7">7 أيام</a><a href="?k=<?= $h($_GET['k']) ?>&d=30">30 يوم</a><a href="?k=<?= $h($_GET['k']) ?>&d=90">90 يوم</a></span> (من <?= $from ?>)</div>
<div class="kpis">
<div class="k"><b><?= count($uAll) ?></b><span>زائر (جهاز) مختلف</span></div>
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
<?php $mx = 1; foreach ($rows as $d) $mx = max($mx, count($d['u'])); foreach (array_reverse($rows, true) as $k => $d): $u = count($d['u']); ?>
<tr><td><?= $k ?></td><td class="n"><span class="bar" style="width:<?= round($u / $mx * 60) ?>px"></span> <?= $u ?></td><td class="n"><?= $d['ev']['view'] ?? 0 ?></td><td class="n"><?= $d['x']['view.s']['1'] ?? 0 ?></td><td class="n"><?= $d['ev']['open'] ?? 0 ?></td><td class="n"><?= $d['ev']['tool'] ?? 0 ?></td><td class="n"><?= $d['ev']['li'] ?? 0 ?></td><td class="n"><?= $d['ev']['pview'] ?? 0 ?></td></tr>
<?php endforeach; ?></table>
<h2>أكتر البوستات تفاعلاً</h2>
<table><tr><th>البوست</th><th>فتح</th><th>أداة</th><th>LinkedIn</th><th>مشاركة</th><th>حفظ</th><th>صفحته</th></tr>
<?php foreach (top($score, 20) as $id => $_): $it = $items[$id] ?? null; ?>
<tr><td><?php if ($it): ?><a href="/p/<?= $h($id) ?>" target="_blank"><?= $h($it['title']) ?></a><?php else: ?><?= $h($id) ?><?php endif; ?></td><?php foreach (['open','tool','li','share','save','pview'] as $e): ?><td class="n"><?= $T['id'][$e][$id] ?? 0 ?></td><?php endforeach; ?></tr>
<?php endforeach; ?></table>
<div class="grid">
<?php
$L = ['view.r' => 'جايين منين', 'view.m' => 'الجهاز', 'view.l' => 'اللغة', 'tool.t' => 'نوع لينك الأداة اللي اتضغط', 'filter.f' => 'الفلاتر المستخدمة', 'search.q' => 'أكتر كلمات البحث'];
$NM = ['view.m' => ['1' => 'موبايل', '0' => 'كمبيوتر'], 'view.r' => ['-' => 'مباشر / تطبيق LinkedIn']];
foreach ($L as $k => $title): $m = top($T['x'][$k] ?? [], 12); if (!$m) continue; ?>
<div><h2><?= $title ?></h2><table><?php foreach ($m as $v => $n): ?><tr><td><?= $h($NM[$k][$v] ?? $v) ?></td><td class="n"><?= $n ?></td></tr><?php endforeach; ?></table></div>
<?php endforeach; ?>
</div>
<?php $AW = ['12'=>[11,21],'13'=>[192,372],'14'=>[289,918],'15'=>[156,1000],'16'=>[110,388],'17'=>[90,165],'18'=>[81,152],'19'=>[269,830],'20'=>[127,327],'21'=>[88,156],'22'=>[70,107],'23'=>[349,395],'24'=>[368,413],'25'=>[137,191],'26'=>[68,161],'27'=>[726,791]]; $awv = array_sum(array_column($AW, 0)); $awp = array_sum(array_column($AW, 1)); ?>
<h2>قبل العداد: أرقام HostGator (AWStats) من 12 لـ 27 نوفمبر</h2>
<p class="sub">دي أرقام الاستضافة زي ما هي، ومش محسوبة بنفس طريقة العداد: بتشمل بوتات وزحف آلي، ومفيهاش فتح بوست ولا ضغطات أدوات. الرقم الأدق للبشر الحقيقيين في الفترة دي حوالي <b>820</b> متصفح (اللي حمّلوا أيقونة الموقع، والبوتات عادة مبتحمّلهاش). عشان كده متفصولة هنا ومش مضافة لأرقام العداد فوق.</p>
<div class="kpis"><div class="k"><b>~820</b><span>متصفح حقيقي (تقدير)</span></div><div class="k"><b><?= number_format($awv) ?></b><span>زيارات AWStats (فيها بوتات)</span></div><div class="k"><b><?= number_format($awp) ?></b><span>صفحات AWStats</span></div></div>
<table style="margin-top:12px"><tr><th>اليوم</th><th>زيارات AWStats</th><th>صفحات</th></tr>
<?php $mx = max(array_column($AW, 0)); foreach (array_reverse($AW, true) as $dd => $r): ?><tr><td>2026-11-<?= $dd ?></td><td class="n"><span class="bar" style="width:<?= round($r[0] / $mx * 60) ?>px;background:#9AA6C4"></span> <?= $r[0] ?></td><td class="n"><?= $r[1] ?></td></tr><?php endforeach; ?></table>
<p class="sub" style="margin-top:24px">العداد الدقيق شغال من 28 نوفمبر. سجلات الزيارات الخام على HostGator بتتمسح كل يوم، فمكانش ينفع نعيد حساب الأيام اللي فاتت بنفس منطق العداد.</p>
</main></body></html>
