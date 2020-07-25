<?php
require_once __DIR__ . '/../vendor/autoload.php';

define('DS', DIRECTORY_SEPARATOR);

$types = [
    'in-song-cards',
    'in-song-score',
    'results-credits-main',
    'results-credits-ads',
    'results-hit-score',
];

$path_data = dirname(__DIR__) . '/input';
$path_tmp = dirname(__DIR__) . '/tmp';
$path_results = $path_tmp . DS . 'results';

function infer_type($results) {
    $alltext = implode("\n", array_map(function ($result) { return $result['DetectedText']; }, $results));

    return null;
}

function filter_results_boundary(array $results, $tl_x, $tl_y, $br_x, $br_y) {
    return array_filter($results, function ($result) use ($tl_x, $tl_y, $br_x, $br_y) {
        return null;
    });
}
