<?php
require_once __DIR__ . '/common.php';
require_once __DIR__ . '/beatmaps_common.php';

use Imagine\Image\Box;
use Imagine\Image\Point;
use SVG\SVG;
use SVG\Nodes\SVGGenericNodeType;
use SVG\Nodes\Shapes\SVGCircle;
use SVG\Nodes\Shapes\SVGLine;
use SVG\Nodes\Structures\SVGDefs;

if (!file_exists($path_beatmap_images)) {
    mkdir($path_beatmap_images, 0777, true);
}

$beatmap_files = array_diff(scandir($path_beatmaps), array('.', '..'));
natsort($beatmap_files);

$verbose = false;

$scale_overall = 1.0;
$scale_vertical = 1.0 * $scale_overall;
$scale_horizontal = 1.0 * $scale_overall;
$scale_subbeat = 1.0 * $scale_overall;

$size_row_base = 200;
$size_row = $size_row_base * $scale_overall;
$size_beat = 20 * $scale_overall;
$size_factor_beat_inner = 0.7;
$offset_beat_inner = ($size_beat - $size_beat * $size_factor_beat_inner) / 2;
$size_sub_beat = $size_row_base * $scale_overall;
$size_slider_line = 12 * $scale_overall;
$size_factor_slider = 0.6;

$color_tap = '#80b1ff';
$color_slider = '#ffe770';
$color_slider_slide = '#dc7';
$color_node_shadow = '#000000';
$color_background = '#152b44';

$image_width = 400 * $scale_horizontal;
$margin_side = 50 * $scale_horizontal;
$margin_top = 100 * $scale_vertical;
$margin_bottom = 50 * $scale_vertical;
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
    $beatmap_secondlast_row_id = array_key_last(array_slice($beatmap, 0, -1));

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

    $image_height = $size_row * $beatmap_last_row_id * $scale_vertical + $margin_top + $margin_bottom;
    $image = new SVG($image_width, $image_height);
    $doc = $image->getDocument();
    $doc->setStyle('background-color', $color_background);
    $font = new \SVG\Nodes\Structures\SVGFont('openGost', dirname($path_beatmaps) . '/RobotoMono-Regular.ttf');
    $doc->addChild($font);

    $defs = new SVGDefs();
    $doc->addChild($defs);

    $filterShadow = new SVGGenericNodeType('filter');
    $filterShadow->setAttribute('id', 'shadow');
    $defs->addChild($filterShadow);
    $feDropShadow = new SVGGenericNodeType('feDropShadow');
    $feDropShadow->setAttribute('dx', '0');
    $feDropShadow->setAttribute('dy', '0');
    $feDropShadow->setAttribute('stdDeviation', '1');
    $feDropShadow->setAttribute('flood-color', $color_node_shadow);
    $feDropShadow->setAttribute('flood-opacity', '0.5');
    $filterShadow->addChild($feDropShadow);

    $filterDiffuseLightOuter = new SVGGenericNodeType('filter');
    $filterDiffuseLightOuter->setAttribute('id', 'diffuseLightOuter');
    $defs->addChild($filterDiffuseLightOuter);
    $feDiffuseLightingOuter = new SVGGenericNodeType('feDiffuseLighting');
    $feDiffuseLightingOuter->setAttribute('in', 'SourceGraphic');
    $feDiffuseLightingOuter->setAttribute('result', 'light');
    $feDiffuseLightingOuter->setAttribute('lighting-color', 'white');
    $filterDiffuseLightOuter->addChild($feDiffuseLightingOuter);
    $fePointLightOuter = new SVGGenericNodeType('fePointLight');
    $fePointLightOuter->setAttribute('x', '150');
    $fePointLightOuter->setAttribute('y', '60');
    $fePointLightOuter->setAttribute('z', '20');
    $filterDiffuseLightOuter->addChild($fePointLightOuter);
    $feCompositeOuter = new SVGGenericNodeType('feComposite');
    $feCompositeOuter->setAttribute('in', 'SourceGraphic');
    $feCompositeOuter->setAttribute('in2', 'light');
    $feCompositeOuter->setAttribute('operator', 'arithmetic');
    $feCompositeOuter->setAttribute('k1', '1');
    $feCompositeOuter->setAttribute('k2', '0');
    $feCompositeOuter->setAttribute('k3', '0');
    $feCompositeOuter->setAttribute('k4', '0');
    $filterDiffuseLightOuter->addChild($feCompositeOuter);

    $filterDiffuseLightInner = new SVGGenericNodeType('filter');
    $filterDiffuseLightInner->setAttribute('id', 'diffuseLightInner');
    $defs->addChild($filterDiffuseLightInner);
    $feDiffuseLightingInner = new SVGGenericNodeType('feDiffuseLighting');
    $feDiffuseLightingInner->setAttribute('in', 'SourceGraphic');
    $feDiffuseLightingInner->setAttribute('result', 'light');
    $feDiffuseLightingInner->setAttribute('lighting-color', 'white');
    $filterDiffuseLightInner->addChild($feDiffuseLightingInner);
    $fePointLightInner = new SVGGenericNodeType('fePointLight');
    $fePointLightInner->setAttribute('x', '-150');
    $fePointLightInner->setAttribute('y', '60');
    $fePointLightInner->setAttribute('z', '20');
    $filterDiffuseLightInner->addChild($fePointLightInner);
    $filterDiffuseLightInner->addChild($feCompositeOuter);

    // Vars to draw sliders
    $last_row_id = null;
    $last_beat_type = null;
    $last_x = null;
    $last_y = null;

    $lines = [];
    $notes = [];
    $last_slider_by_group = [];
    // Analyze the notes first
    foreach ($beatmap as $row_id => $row) {
        if ($row_id > $beatmap_last_row_id) {
            continue;
        }

        foreach ($row as $boat_id => $beat) {
            $beat_offset_y = $image_height + $margin_top - ($row_id + 18) * $size_row * $scale_vertical + -1 * $size_sub_beat * $scale_subbeat * $beat['vertical_offset'] * $scale_vertical / $max_vertical_offset;
            $beat_offset_x = $beat_x_start
                + $beat_x_width * $scale_horizontal * $beat['column_index'] / $max_column_index;

            $is_slider = $beat['beat_type'] !== 0;
            $is_slider_start = $is_slider && in_array($beat['beat_type'], $beat_slider_start);
            $slider_group = $is_slider ? $beat_slider_groups[$beat['beat_type']] : 0;
            $color = $is_slider ? $color_slider : $color_tap;
            $note = [
                'row' => $row_id,
                'x' => $beat_offset_x,
                'y' => $beat_offset_y,
                'size' => $is_slider && !$is_slider_start ? $size_beat * $size_factor_slider : $size_beat,
                'color' => $color,
                'beat' => $beat,
                'type' => $is_slider ? 'slider' : 'tap',
                'slider_group' => $slider_group,
                'slider_start' => $is_slider_start,
                'slider_continue' => $is_slider && !$is_slider_start,
            ];
            $notes[] = $note;

            if ($is_slider_start) {
                $last_slider_by_group[$slider_group] = $note;
            }

            if ($is_slider && !$is_slider_start && isset($last_slider_by_group[$slider_group])) {
                $line = [
                    'note_last' => $last_slider_by_group[$slider_group],
                    'note_current' => $note,
                ];
                $lines[] = $line;
                $last_slider_by_group[$slider_group] = $note;
            }

            if ($verbose) {
                $doc->addChild(
                    (new \SVG\Nodes\Texts\SVGText(
                        sprintf(
                            '[#%02d-%03d] 0x%02s',
                            $row_id,
                            $beat['note_id'],
                            strtoupper(dechex($beat['beat_type']))
                        ),
                        $beat_offset_x + 15,
                        $beat_offset_y + 30
                    ))
                    ->setFont($font)
                    ->setSize(12)
                    ->setStyle('stroke', '#333')
                    ->setStyle('stroke-width', 1)
                );
            }

            $last_x = $beat_offset_x;
            $last_y = $beat_offset_y;
            $last_beat_type = $beat['beat_type'];
        }
        $last_row_id = $row_id;
        $last_x = null;
        $last_y = null;
    }

    foreach ($lines as $line) {
        $doc->addChild(
            (new SVGLine($line['note_last']['x'], $line['note_last']['y'], $line['note_current']['x'], $line['note_current']['y']))
                ->setStyle('stroke', $color_slider_slide)
                ->setStyle('stroke-width', $size_slider_line . 'px')
        );
    }

    foreach (array_reverse($notes) as $note) {
        $circleOuter = (new SVGCircle($note['x'], $note['y'], $note['size']))
            ->setStyle('fill', $note['color'])
            ->setStyle('filter', 'url(#shadow), url(#diffuseLightOuter)');
        $doc->addChild($circleOuter);

        if ($note['slider_continue']) {
            $circleInner = (new SVGCircle(
                $note['x'] + $offset_beat_inner,
                $note['y'] + $offset_beat_inner,
                $note['size'] * $size_factor_beat_inner,
            ));
            $circleInner
                ->setStyle('fill', $note['color'])
                ->setStyle('filter', 'url(#diffuseLightInner)')
            ;
            $doc->addChild($circleInner);
        }
    }

    $filename = sprintf(
        '%s%s%s - %s - %s.svg',
        $path_beatmap_images,
        DS,
        basename($songdata['dalcom_beatmap_filename'], '.json'),
        $songdata['dalcom_song_filename'],
        $songdata['difficulty']
    );
    file_put_contents($filename, $image);
    echo sprintf('Wrote "%s"', $filename) . PHP_EOL;
}

echo 'Done.' . PHP_EOL;
