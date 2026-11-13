<?php
require __DIR__ . '/_rb.php';
header('Content-Type: application/xml; charset=utf-8');
$items = rb_items(); $upd = rb_data()['updated'] ?? date('Y-m-d');
$u = function ($loc, $mod, $freq, $pri) { echo "<url><loc>" . h($loc) . "</loc>" . ($mod ? "<lastmod>" . h($mod) . "</lastmod>" : '') . "<changefreq>$freq</changefreq><priority>$pri</priority></url>\n"; };
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
$u(RB_SITE . '/', $upd, 'daily', '1.0');
$u(RB_SITE . '/posts', $upd, 'daily', '0.9');
$u(RB_SITE . '/guide.html', null, 'monthly', '0.4');
foreach (rb_sections() as $k => $v) $u(RB_SITE . '/section/' . $k, $upd, 'daily', '0.8');
foreach (rb_all_tags() as $s => $t) if ($t['n'] >= 2) $u(RB_SITE . '/tag/' . $s, $upd, 'weekly', '0.6');
foreach ($items as $i) { $d = $i['added'] ?? ''; if (!preg_match('/^\d{4}-\d\d-\d\d$/', $d)) $d = $i['date'] ?? ''; $u(RB_SITE . '/p/' . $i['id'], preg_match('/^\d{4}-\d\d-\d\d$/', $d) ? $d : null, 'monthly', '0.7'); }
echo '</urlset>';
