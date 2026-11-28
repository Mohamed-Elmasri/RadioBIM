<?php
// Anonymous usage counter for radiobim.com. Stores daily counts only: no IPs, no personal data.
header('Cache-Control: no-store');
$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $ua === '' || preg_match('/bot|crawl|spider|slurp|headless|preview|facebookexternalhit|curl|wget|python/i', $ua)) { http_response_code(204); exit; }
$raw = file_get_contents('php://input', false, null, 0, 2048);
$j = json_decode($raw, true);
if (!is_array($j)) { http_response_code(204); exit; }
$EV = ['view','open','li','tool','share','save','install','install_ok','search','filter','guide','pview'];
$e = $j['e'] ?? ''; if (!in_array($e, $EV, true)) { http_response_code(204); exit; }
$id = (isset($j['id']) && preg_match('/^[a-f0-9]{8}$/', (string)$j['id'])) ? $j['id'] : null;
$x = is_array($j['x'] ?? null) ? $j['x'] : [];
$X = ['s' => '/^[01]$/', 'm' => '/^[01]$/', 'r' => '/^[a-z0-9.\-]{1,40}$/', 'l' => '/^(ar|en)$/', 't' => '/^[A-Za-z]{2,12}$/', 'f' => '/^[a-z]{2,12}$/', 'q' => '/^.{2,40}$/u'];
$xx = [];
foreach ($X as $k => $re) if (isset($x[$k]) && preg_match($re, (string)$x[$k])) $xx[$k] = $k === 'q' ? mb_strtolower(trim((string)$x[$k])) : (string)$x[$k];
$vid = (isset($j['v']) && preg_match('/^[a-z0-9]{8,24}$/', (string)$j['v'])) ? substr(sha1('rb' . $j['v']), 0, 10) : null;
$dir = dirname(__DIR__) . '/radiobim-stats';
if (!is_dir($dir)) @mkdir($dir, 0700, true);
date_default_timezone_set('Africa/Cairo');
$day = date('Y-m-d'); $f = $dir . '/' . date('Y-m') . '.json';
$h = @fopen($f, 'c+'); if (!$h) { http_response_code(204); exit; }
flock($h, LOCK_EX);
$data = json_decode(stream_get_contents($h) ?: '{}', true) ?: [];
$d = $data[$day] ?? ['ev' => [], 'u' => [], 'id' => [], 'x' => []];
$d['ev'][$e] = ($d['ev'][$e] ?? 0) + 1;
if ($vid && $e === 'view') $d['u'][$vid] = 1;
if ($id) $d['id'][$e][$id] = ($d['id'][$e][$id] ?? 0) + 1;
foreach ($xx as $k => $v) $d['x'][$e . '.' . $k][$v] = ($d['x'][$e . '.' . $k][$v] ?? 0) + 1;
$data[$day] = $d;
ftruncate($h, 0); rewind($h); fwrite($h, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_FORCE_OBJECT)); fflush($h); flock($h, LOCK_UN); fclose($h);
http_response_code(204);
