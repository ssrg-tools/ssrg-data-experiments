<?php
require_once __DIR__ . '/common.php';
require_once __DIR__ . '/beatmaps_common.php';

$config = include $config_file;
$dbconfig = $config['db'];

$beatmap_files = array_diff(scandir($path_beatmaps), array('.', '..'));
natsort($beatmap_files);


// $beatmap_files = ['1001_4.seq'];

$verbose = false;

$missing_vertical_offset = [];
$duplicate_notes = [];
$column_indexex_by_difficulty = [];
$vertical_offsets_by_difficulty = [];
$column_indexex_by_difficulty = [];
$beat_type_by_difficulty = [];

$songinfos = [];

foreach ($beatmap_files as $beatmap_file) {
    if (!is_file($path_beatmaps . DS . $beatmap_file) || !preg_match('/\\.seq$/', $beatmap_file)) {
        continue;
    }

    echo sprintf("Scanning file '%s'...   \t", $beatmap_file);
    $filename_matches = [];
    preg_match('/^(?P<song_id>\d+)_(?P<difficulty_id>\d{1,2})\.seq$/', $beatmap_file, $filename_matches);
    list('song_id' => $song_id, 'difficulty_id' => $difficulty_id) = $filename_matches;
    $difficulty = $difficulty_map[$difficulty_id];

    $contents = file_get_contents($path_beatmaps . DS . $beatmap_file);
    $contents_length = strlen($contents);

    $song_filename_start = strpos($contents, 'GF_') ?: stripos($contents, 'GFriend_'); // nice little hack :D
    if ($song_filename_start === false) {
        echo sprintf('Warning: Could not find song filename in "%s".', $beatmap_file) . PHP_EOL;
        continue;
    }
    $song_filename_length = unpack('V', $contents, $song_filename_start - 4)[1];
    $song_file = substr($contents, $song_filename_start, $song_filename_length - 2);

    echo sprintf("Song file: %s [%s]", str_pad($song_file, 28), $difficulty) . PHP_EOL;

    $beatmap_offset = $song_filename_start + $song_filename_length + 2316;

    $has_more_data = true;

    $used_column_index = [];
    $used_beat_type = [];
    $used_vertical_offset = [];
    $used_counters = [];
    $count_notes_total = 0; // including combos
    $count_taps = 0;
    $count_sliders = 0; // without combos
    $count_sliders_total = 0; // including combos

    $rows = [];

    // each 'line' has 24 bytes - no idea how many variables
    // - type 3 - 1 byte (7 bit?) - vertical offset within a row?
    // - counter - 2 bytes
    // 6 empty bytes
    // - type 1 - 1 byte
    // 7 empty bytes
    // - type 2 - 1 byte (2 bits?)
    $line_length = 24;
    $current_offset = $beatmap_offset;
    $last_counter = -1;
    while ($has_more_data) {
        $count_notes_total++;
        $nextline = substr($contents, $current_offset, $line_length);
        // echo sprintf(' next data: %s', bin2hex($nextline)) . PHP_EOL;
        $vertical_offset = unpack('C', $nextline, 0)[1];
        $used_vertical_offset[] = $vertical_offset;
        // $is_continuation = $vertical_offset & 0x80;
        $counter = unpack('v', $nextline, 1)[1];
        $is_continuation = $last_counter === $counter;
        $used_counters[] = $counter;

        $column_index = unpack('C', $nextline, 8)[1];
        $used_column_index[] = $column_index;

        $beat_type = unpack('C', $nextline, 16)[1];
        $used_beat_type[] = $beat_type;
        if ($beat_type === 0x00) {
            $count_taps++;
        } else {
            $count_sliders_total++;
            if ($beat_type === 0x0B) {
                $count_sliders++;
            }
        }

        if (!isset($column_indexes[$column_index])) {
            echo sprintf('Please add %s / 0x%02.s / 0b%s to column_index.', $column_index, dechex($column_index), decbin($column_index)) . PHP_EOL;
        }
        if (!isset($beat_types2[$beat_type])) {
            echo sprintf('Please add %s / 0x%02.s / 0b%s to beat_type.', $beat_type, dechex($beat_type), decbin($beat_type)) . PHP_EOL;
        }
        if (!isset($beat_types3[$vertical_offset])) {
            $missing_vertical_offset[] = $vertical_offset;
            $current_offset = $current_offset + $line_length;
            continue;
            // echo sprintf('Please add %s / 0x%02.s / 0b%s to vertical_offset.', $vertical_offset, dechex($vertical_offset), decbin($vertical_offset)) . PHP_EOL;
        }

        if ($verbose) {
            echo sprintf(
                '  counter: %04.s %s [ %03s ] [ %s <%8s> ] [ %s <%8s> ]',
                $counter,
                $is_continuation ? '+' : ' ',
                $vertical_offset,
                $column_indexes[$column_index]['render'],
                decbin2($column_index),
                $beat_types2[$beat_type]['render'],
                decbin2($beat_type)
            );
            echo PHP_EOL;
        }

        $current_row = $rows[$counter] ?? [];
        $temp_column_index = sprintf('%s-%s', $column_index, $vertical_offset);
        $current_note = [
            'note_id' => $count_notes_total,
            'column_index' => $column_index,
            'beat_type' => $beat_type,
            'vertical_offset' => $vertical_offset,
        ];
        if (isset($current_row[$temp_column_index])) {
            $duplicate_notes[] = sprintf(
                'Duplicate note: %s [%s] - row %s column %s - current %s old %s',
                $song_file,
                $difficulty,
                $counter,
                $temp_column_index,
                json_encode($current_note),
                json_encode($current_row[$temp_column_index])
            );
        }
        $current_row[$temp_column_index] = $current_note;
        $rows[$counter] = $current_row;

        $current_offset = $current_offset + $line_length;
        $last_counter = $counter;

        $next_offset = $current_offset + $line_length;
        if ($next_offset > $contents_length) {
            if ($current_offset === $contents_length) {
                echo 'End of data is perfectly flush, 0 bytes remaining!' . PHP_EOL;
            } else {
                $remaining_data = substr($contents, $current_offset);
                echo sprintf(' :: remaining data: <%d, %d> [%d] %s', $next_offset, $contents_length, strlen($remaining_data), bin2hex($remaining_data)) . PHP_EOL;
            }
            $has_more_data = false;
        }
    }

    $count_notes_nocombo = $count_taps + $count_sliders;
    $songinfo = ($songinfos[$song_id] ?? []) + [
        'dalcom_song_id' => $song_id,
        'dalcom_song_filename' => $song_file,
        'date_processed' => date("Y-m-d H:i:sP"),
    ];
    $songinfo_bydifficulty = [
        'difficulty' => $difficulty,
        'difficulty_id' => $difficulty_id,
        'dalcom_beatmap_filename' => $beatmap_file,
        'beatmap_fingerprint' => hash_file('sha256', $path_beatmaps . DS . $beatmap_file),
        'count_notes_total' => $count_notes_total,
        'count_notes_nocombo' => $count_notes_nocombo,
        'count_taps' => $count_taps,
        'count_sliders_nocombo' => $count_sliders,
        'count_sliders_total' => $count_sliders_total,
        'date_processed' => date("Y-m-d H:i:sP"),
        'guid' => guid_generate($dbconfig['host'], $dbconfig['name'], strtotime(date("Y-m-d H:i:sP"))),
    ];

    $songinfo_bydifficulties = $songinfo['bydifficulties'] ?? [];
    $songinfo_bydifficulties[$difficulty] = $songinfo_bydifficulty;
    $songinfo['bydifficulties'] = $songinfo_bydifficulties;
    $songinfos[$song_id] = $songinfo;

    $beatmap = $songinfo + $songinfo_bydifficulty + [
        'map' => $rows,
    ];
    file_put_contents($path_beatmaps . DS . $beatmap_file . '.json', json_encode($beatmap, JSON_PRETTY_PRINT));

    $used_column_index = array_unique($used_column_index);
    $used_beat_type = array_unique($used_beat_type);
    $used_vertical_offset = array_unique($used_vertical_offset);
    $used_counters = array_unique($used_counters);

    $column_indexex_by_difficulty[$difficulty] = array_merge($used_column_index, $column_indexex_by_difficulty[$difficulty] ?? []);
    $vertical_offsets_by_difficulty[$difficulty] = array_merge($used_vertical_offset, $column_indexex_by_difficulty[$difficulty] ?? []);
    $beat_type_by_difficulty[$difficulty] = array_merge($used_beat_type, $beat_type_by_difficulty[$difficulty] ?? []);

    echo sprintf('Lines: %02d                                     (true note count?)', $count_notes_total) . PHP_EOL;
    echo sprintf(
        'Used counters: %dx - min %d - max %d          (rows for notes?)',
        count($used_counters),
        min(...$used_counters),
        max(...$used_counters)
    ) . PHP_EOL;
    echo sprintf(
        'Used column_index: %s',
        format_thingies_many($used_column_index)
    ) . PHP_EOL;
    echo sprintf(
        'Used beat_type: %s',
        format_thingies_many($used_beat_type)
    ) . PHP_EOL;
    // echo implode("\n", array_map('format_thingies', $used_beat_type)) . PHP_EOL;
    echo sprintf(
        'Used vertical_offset: %dx',
        count($used_vertical_offset)
    ) . PHP_EOL;
    // echo implode("\n", array_map('format_thingies', $used_vertical_offset)) . PHP_EOL;

    echo PHP_EOL;
    echo PHP_EOL;
}


echo PHP_EOL;
echo PHP_EOL;

echo 'Global stuff:' . PHP_EOL;

echo 'Column indexes' . PHP_EOL;
foreach ($column_indexex_by_difficulty as $difficulty => $used_column_index) {
    echo sprintf('{[%s]}: %s', $difficulty, format_thingies_many($used_column_index)) . PHP_EOL;
}

echo 'Vertical offsets' . PHP_EOL;
foreach ($vertical_offsets_by_difficulty as $difficulty => $used_vertical_offset) {
    echo sprintf('{[%s]}: %s', $difficulty, format_thingies_many($used_beat_type)) . PHP_EOL;
}

echo 'Used value type 2' . PHP_EOL;
foreach ($beat_type_by_difficulty as $difficulty => $used_beat_type) {
    echo sprintf('{[%s]}: %s', $difficulty, format_thingies_many($used_beat_type)) . PHP_EOL;
}

echo 'Duplicate notes' . PHP_EOL;
print_r($duplicate_notes);


echo 'Song infos' . PHP_EOL;
foreach ($songinfos as &$songinfo) {
    $beatmap_fingerprint = '';
    foreach ($songinfo['bydifficulties'] as $songinfo_bydifficulty) {
        $beatmap_fingerprint = hash('sha256', $beatmap_fingerprint . $songinfo_bydifficulty['beatmap_fingerprint']);
    }
    $songinfo['beatmap_fingerprint'] = $beatmap_fingerprint;
}
print_r($songinfos);

$songinfos_data = [
    'date' => date("Y-m-d H:i:sP"),
    'songinfos' => $songinfos,
    'guid' => guid_generate(),
];
file_put_contents(sprintf($path_beatmaps . DS . 'songinfos-%s-%s.json', date("Y-m-d_H-i-s"), $songinfos_data['guid']), json_encode($songinfos_data, JSON_PRETTY_PRINT));

foreach (array_unique($missing_vertical_offset) as $vertical_offset) {
    // echo sprintf('Please add %s / 0x%02.s / 0b%s to vertical_offset.', $vertical_offset, dechex($vertical_offset), decbin($vertical_offset)) . PHP_EOL;
    // echo sprintf('    0x%1$02s => [ \'name\' => \'%1$02s\', \'render\' => \'X %1$02s\', ],', strtoupper(dechex($vertical_offset))) . PHP_EOL;
}
