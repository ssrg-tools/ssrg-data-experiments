<?php
require_once __DIR__ . '/common.php';

if (!file_exists($path_results) || !is_dir($path_results)) {
    echo 'No results yet!';
    die;
}

$result_files = array_diff(scandir($path_results), array('.', '..'));
$result_files = array_map(function ($result_file) use ($path_results) {
    return json_decode(file_get_contents($path_results . DS . $result_file), true);
}, $result_files);
usort($result_files, function ($a, $b) {
    return strcmp($a['filename'], $b['filename']);
});

foreach ($result_files as $result) {
    $screen = infer_screen($result['text']);
    echo sprintf('%s: %s', $result['filename'], $screen['type']) . PHP_EOL;
}

