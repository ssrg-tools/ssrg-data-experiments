<?php
/**
 * This file updates records in the database.
 *   - fills in missing GUIDs (for manually entered records)
 */
require_once __DIR__ . '/common.php';

use Aura\Sql\ExtendedPdo;

if (!file_exists($config_file)) {
    echo 'Please create a config file.';
    exit(1);
}

$config = include $config_file;
$dbconfig = $config['db'];

$dsn = "mysql:host=${dbconfig['host']};dbname=${dbconfig['name']};charset=utf8mb4";

try {
    $pdo = $pdo = new ExtendedPdo(
        $dsn,
        $dbconfig['user'],
        $dbconfig['pass'],
        [], // driver attributes/options as key-value pairs
        []  // queries to execute after connection
    );
} catch (Exception $e) {
    print_r($e);
    exit('Something weird happened'); //something a user can understand
}

// Update GUIDs
echo 'Updating GUIDs.' . PHP_EOL;

$guid_tables = [
    'divisions' => [ 'date_column' => false ],
    'league_ranking' => [ 'date_column' => 'date' ],
    'log_credits' => [ 'date_column' => 'date' ],
    'log_diamonds' => [ 'date_column' => 'date' ],
    'log_diamonds_ads' => [ 'date_column' => 'date' ],
    'log_drops' => [ 'date_column' => 'date' ],
    'song_clear_cards' => [ 'date_column' => false ],
    'song_clears_v2' => [ 'date_column' => 'date' ],
    'songs' => [ 'date_column' => false ],
    'superstar_games' => [ 'date_column' => false ],
    'themes' => [ 'date_column' => false ],
    'user_credentials' => [ 'date_column' => 'created' ],
    'users' => [ 'date_column' => 'created' ],
];

foreach ($guid_tables as $table_name => $table_options) {
    $count = $pdo->fetchCol("SELECT COUNT(*) FROM `$table_name` WHERE guid IS NULL")[0];
    if (!$count) {
        echo sprintf('Skipping table "%s" - no entries to update.', $table_name) . PHP_EOL;
        continue;
    }
    echo sprintf('Table "%s" [%dx]: ', $table_name, $count);
    if ($table_options['date_column']) {
        foreach ($pdo->yieldPairs("SELECT id, `${table_options['date_column']}` FROM `$table_name` WHERE guid IS NULL") as $row_id => $date) {
            $pdo->perform(
                "UPDATE `$table_name` SET guid = :guid WHERE id = :id",
                [ 'guid' => guid_generate($dbconfig['host'], $dbconfig['name'], strtotime($date)), 'id' => $row_id ]
            );
            echo '.';
        }
    } else {
        foreach ($pdo->yieldCol("SELECT id FROM `$table_name` WHERE guid IS NULL") as $row_id) {
            $pdo->perform(
                "UPDATE `$table_name` SET guid = :guid WHERE id = :id",
                [ 'guid' => guid_generate($dbconfig['host'], $dbconfig['name']), 'id' => $row_id ]
            );
            echo '.';
        }
    }
    echo PHP_EOL;
}
