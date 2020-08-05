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

$scale_overall = 1.0;
$scale_vertical = 1.0 * $scale_overall;
$scale_horizontal = 1.0 * $scale_overall;
$scale_subbeat = 1.0 * $scale_overall;

$size_row = 40 * $scale_overall;
$size_beat = 15 * $scale_overall;
$size_sub_beat = 15 * $scale_overall;
$size_slider_line = 6 * $scale_overall;

$color_tap = '59e';
$color_slider = 'ed8';
$color_slider_slide = 'dc7';

$image_width = 200 * $scale_horizontal;
$margin_side = 25 * $scale_horizontal;
$margin_top = 50 * $scale_vertical;
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

    echo $songdata['dalcom_beatmap_filename'] . ' [' . $songdata['difficulty'] . ']' . PHP_EOL;

    $beatmap_last_row_id = array_key_last($beatmap);

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

    $image_height = $size_row * $beatmap_last_row_id * $scale_vertical + $margin_top;
    $image = $imagine->create(new Box($image_width, $image_height));

    // Vars to draw sliders
    $last_row_id = null;
    $last_beat_type = null;
    $last_x = null;
    $last_y = null;

    foreach (array_reverse($beatmap) as $row_id => $row) {
        if ($row_id > $beatmap_last_row_id) {
            continue;
        }

        foreach ($row as $boat_id => $beat) {
            $beat_offset_y = $image_height - $row_id * $size_row * $scale_vertical + $size_sub_beat * $scale_subbeat * ($beat['vertical_offset'] + 1) * $scale_vertical / $max_vertical_offset;
            $beat_offset_x = $beat_x_start
                + $beat_x_width * $scale_horizontal * ($beat['column_index'] + 1) / $max_column_index;

            if ($beat['beat_type'] !== 0 && $last_beat_type !== 0 && $last_x && $last_y) {
                $image->draw()
                    ->line(new Point($last_x, $last_y), new Point($beat_offset_x, $beat_offset_y), $image->palette()->color($color_slider_slide), $size_slider_line);
            }

            $color = $beat['beat_type'] === 0 ? $color_tap : $color_slider;
            $image->draw()
                ->ellipse(new Point($beat_offset_x, $beat_offset_y), new Box($size_beat, $size_beat), $image->palette()->color($color), true);

            $last_x = $beat_offset_x;
            $last_y = $beat_offset_y;
            $last_beat_type = $beat['beat_type'];
        }
        $last_row_id = $row_id;
        $last_x = null;
        $last_y = null;

    }

    $image->save(sprintf(
        '%s%s%s - %s.png',
        $path_beatmap_images,
        DS,
        basename($songdata['dalcom_beatmap_filename'], '.json'),
        $songdata['difficulty']
    ));
}

echo 'Done.' . PHP_EOL;
