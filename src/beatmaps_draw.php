<?php
require_once __DIR__ . '/common.php';

use Imagine\Image\Box;
use Imagine\Image\Point;

if (!file_exists($path_beatmap_images)) {
    mkdir($path_beatmap_images, 0777, true);
}

$beatmap_files = array_diff(scandir($path_beatmaps), array('.', '..'));
natsort($beatmap_files);

// $beatmap_files = array_slice($beatmap_files, 0, 9);

$imagine = new Imagine\Imagick\Imagine();

$size_row = 15;
$size_beat = 10;
$size_sub_beat = 5;

$scale_vertical = 1.2;
$scale_horizontal = 1.0;
$scale_subbeat = 1;

$color_tap = '59e';
$color_slider = 'ed8';

$image_width = 150;
$margin_side = 25;
$beat_x_start = $margin_side;
$beat_x_end   = $image_width - $margin_side;
$beat_x_width = $beat_x_end - $beat_x_start;

foreach ($beatmap_files as $beatmap_file) {
    if (!is_file($path_beatmaps . DS . $beatmap_file) || !preg_match('/\\.seq\\.json$/', $beatmap_file)) {
        continue;
    }

    echo sprintf('Processing file %s - ', $beatmap_file);

    $songdata = json_decode(file_get_contents($path_beatmaps . DS . $beatmap_file), true);
    $beatmap = $songdata['map'];

    echo $songdata['song_filename'] . ' [' . $songdata['difficulty'] . ']' . PHP_EOL;

    $filtered_beatmap = array_filter($beatmap);
    array_pop($filtered_beatmap);
    $last_row_id = array_key_last($filtered_beatmap);

    $image_height = ($size_row + $size_sub_beat * $songdata['difficulty_id']) * $last_row_id;

    $size  = new Box($image_width, $image_height);
    $image = $imagine->create($size);

    $used_vertical_offsets = [];
    $used_column_indexes = [];
    foreach ($beatmap as $row_id => $row) {
        foreach ($row as $boat_id => $beat) {
            $used_column_indexes[] = $beat['column_index'];
            $used_vertical_offsets[] = $beat['vertical_offset'];
        }
    }
    $min_column_index = min(...$used_column_indexes);
    $max_column_index = max(...$used_column_indexes);
    $min_vertical_offset = min(...$used_vertical_offsets);
    $max_vertical_offset = max(...$used_vertical_offsets);

    foreach ($beatmap as $row_id => $row) {
        if ($row_id > $last_row_id) {
            continue;
        }
        // echo $row_id;
        foreach ($row as $boat_id => $beat) {
            $beat_offset_height = $image_height - $row_id * $size_row * $scale_vertical + $size_sub_beat * $scale_subbeat * ($beat['vertical_offset'] ?: 1) * $scale_vertical / $max_vertical_offset;
            $beat_offset_width = $beat_x_start
                + $beat_x_width * $scale_horizontal * ($beat['column_index'] + 1) / $max_column_index;

            // echo sprintf('%03.02fx%04.02f - Index %s', $beat_offset_width, $beat_offset_height, $beat['column_index']) . PHP_EOL;

            // echo ' ' . $boat_id . ' ' . $beat_offset_height;
            $color = $beat['beat_type'] === 0 ? $color_tap : $color_slider;
            $image->draw()
                ->ellipse(new Point($beat_offset_width, $beat_offset_height), new Box($size_beat, $size_beat), $image->palette()->color($color), true);
        }
        // echo PHP_EOL;
    }

    $image->save(sprintf(
        '%s%s%s - %s.png',
        $path_beatmap_images,
        DS,
        basename($songdata['song_filename'], '.json'),
        $songdata['difficulty']
    ));

    // echo '400x' . $image_height . PHP_EOL;

// break;
}

echo 'Done.' . PHP_EOL;
