<?php
require_once __DIR__ . '/common.php';

use Aws\Rekognition\RekognitionClient;

use Aws\Exception\AwsException;

$rekognition = new RekognitionClient([
    'version' => 'latest',
    'region' => 'us-east-2'
]);

$files = array_diff(scandir($path_data), array('.', '..', '.gitkeep'));

if (!file_exists($path_results) || !is_dir($path_results)) {
    mkdir($path_results, 0777, true);
}

$time_start = microtime(true);

foreach ($files as $file) {
    $time_file_start = microtime(true);
    $fullpath = $path_data . DS . $file;
    $filehash = sha1_file($fullpath);
    $results_file = $path_tmp . DS . 'results' . DS . $filehash . '.json';

    if (file_exists($results_file)) {
        echo sprintf(' - [.] File "%s" already processed: %s', $file, $results_file) . PHP_EOL;
        continue;
    }

    $result = $rekognition->detectText([
        'Image' => [
            'Bytes' => file_get_contents($fullpath, 'r'),
        ],
    ]);

    $data = [
        'filename' => $file,
        'fullpath' => $fullpath,
        'sha1' => $filehash,
        'md5' => md5_file($fullpath),
        'processed' => gmdate("Y-m-d\TH:i:s\Z"),
        'size' => getimagesize($fullpath),
        'text' => $result['TextDetections'],
    ];

    file_put_contents($results_file, json_encode($data, JSON_PRETTY_PRINT));
    $time_file_end = microtime(true);
    echo sprintf(' - [X] File "%s" processed (%.2fs): %s', $file, $time_file_end - $time_file_start, $results_file) . PHP_EOL;
}

$time_end = microtime(true);
echo sprintf('Done with Rekognition (%.2fs).', $time_end - $time_start) . PHP_EOL;
