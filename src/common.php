<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Tuupola\Base62Proxy as Base62;

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

define('TYPE_GAME_LOADING', 'game-loading');
define('TYPE_GAME_START', 'game-start');
define('TYPE_GAME_END', 'game-end');
define('TYPE_RESULT_CREDITS', 'result-credits');
define('TYPE_RESULT_CREDITS_ADS', 'result-credits-ads');
define('TYPE_RESULT_HIT_SCORE', 'result-hit-score');
define('TYPE_RESULT_CARD_SCORE', 'result-card-score');

define('TYPE_CARD_OPEN_CHALLENGE', 'card-open-challenge');
define('TYPE_CARDS_REWARD_EVENT', 'cards-reward-event');

define('BK_ON_DUPLICATE', 'bk-on-duplicate');
define('BK_OOD', 'bk-ood');

define('BEHAVIOUR_ALLOW', 'allow');
define('BEHAVIOUR_NEW_SONG', 'new-song');

$default_screen = [
    'supported' => true,
    'behaviours' => [
        BK_ON_DUPLICATE => BEHAVIOUR_ALLOW,
        BK_OOD => BEHAVIOUR_NEW_SONG,
    ],
];

global $screens;
$screens = [
    TYPE_CARD_OPEN_CHALLENGE => [
        'type' => TYPE_CARD_OPEN_CHALLENGE,
        'infer-words' => [
            'CARD OPEN',
            'AUTO EQUIP',
            'You obtained the reward.',
        ],
    ] + $default_screen,
    // TODO - make this for purchases and attendance too
    // TODO - multi-page
    TYPE_CARDS_REWARD_EVENT => [
        'type' => TYPE_CARDS_REWARD_EVENT,
        'infer-words' => [
            'EVENT REWARDS',
            'AUTO EQUIP',
        ],
        'supported' => false,
    ] + $default_screen,
    TYPE_GAME_LOADING => [
        'type' => TYPE_GAME_LOADING,
        'infer-words' => [
            'WORLD RECORD',
            'MY RECORD',
        ],
        'behaviours' => [
            BK_ON_DUPLICATE => BEHAVIOUR_NEW_SONG,
            BK_OOD => BEHAVIOUR_NEW_SONG,
        ],
    ] + $default_screen,
    TYPE_GAME_START => [
        'type' => TYPE_GAME_START,
        'infer-words' => [
            'SCORE',
            'ENERGY',
            [ 'OR' => [
                'THEME BONUS',
                'No Theme',
            ]],
        ],
        'behaviours' => [
            BK_ON_DUPLICATE => BEHAVIOUR_NEW_SONG,
            BK_OOD => BEHAVIOUR_NEW_SONG,
        ],
    ] + $default_screen,
    TYPE_GAME_END => [
        'type' => TYPE_GAME_END,
        'infer-words' => [
            'S SCORE',
            [ 'NOT' => [
                'THEME BONUS',
                'No Theme',
            ]],
        ],
    ] + $default_screen,
    TYPE_RESULT_CREDITS => [
        'type' => TYPE_RESULT_CREDITS,
        'infer-words' => [
            'GAME REWARD',
            'More Item',
        ],
    ] + $default_screen,
    TYPE_RESULT_HIT_SCORE => [
        'type' => TYPE_RESULT_HIT_SCORE,
        'infer-words' => [
            'HIT RESULT',
            'SUPER PERFECT',
            'GOOD',
        ],
    ] + $default_screen,
    TYPE_RESULT_CARD_SCORE => [
        'type' => TYPE_RESULT_CARD_SCORE,
        'infer-words' => [
            'CARD SCORE',
            [ 'NOT' => [
                'SUPER PERFECT',
                'GOOD',
            ]],
        ],
    ] + $default_screen,
];

$path_data = dirname(__DIR__) . '/input';
$path_tmp = dirname(__DIR__) . '/tmp';
$path_results = $path_tmp . DS . 'results';

function infer_screen(array $results) {
    $alltext = implode("\n", array_map(function ($result) { return $result['DetectedText']; }, $results));
    // echo "\n\n\n$alltext\n\n\n\n";
    global $screens;
    foreach ($screens as $screen) {
        $inferWords = $screen['infer-words'];
        $matchAll = all($inferWords, curry_fa('infer_matcher', $alltext));
        if ($matchAll) {
            return $screen;
        }
    }

    return null;
}

function infer_matcher(string $alltext, $expression): bool {
    if (is_string($expression)) {
        return strpos($alltext, $expression) !== false;
    }

    if (isset($expression['OR'])) {
        return any($expression['OR'], curry_fa('infer_matcher', $alltext));
    }

    if (isset($expression['NOT'])) {
        return !infer_matcher($alltext, $expression['NOT']);
    }

    return false;
}

function filter_results_boundary(array $results, $tl_x, $tl_y, $br_x, $br_y) {
    return array_filter($results, function ($result) use ($tl_x, $tl_y, $br_x, $br_y) {
        //
        return null;
    });
}

function number_remove_format(string $input) {
    return preg_replace('/\D/g', '', $input);
}

/**
 * Returns true when a predicate returns true when a predicate applies to all
 * elements of the given list or iterable.
 *
 * Note that this function short-circuits when the predicate does not apply to
 * at least one element.
 *
 * @return bool
 */
function all($list, callable $bool_predicate = null)
{
    if (!$bool_predicate) {
        $bool_predicate = 'id';
    }
    foreach ($list as $key => $value) {
        if (!$bool_predicate($value, $key)) {
            return false;
        }
    }
    return true;
}

/**
 * Returns true if the predicate applies to at least one element of a given list
 * or iterable.
 *
 * Note that this function short-circuits when successful.
 *
 * @return bool
 */
function any($list, callable $bool_predicate = null)
{
    if (!$bool_predicate) {
        $bool_predicate = 'id';
    }
    foreach ($list as $key => $value) {
        if ($bool_predicate($value, $key)) {
            return true;
        }
    }
    return false;
}

/**
 * Curries the first argument for a callable.
 *
 * @return callable
 */
function curry_fa(callable $fun, $first_arg /* , ... */)
{
    $first_args = array_slice(func_get_args(), 1);
    return function () use ($fun, $first_args) {
        return call_user_func_array(
            $fun,
            array_merge($first_args, func_get_args())
        );
    };
}

/**
 * Curries the last argument for a callable.
 *
 * @return callable
 */
function curry_la(callable $fun, $last_arg /* , ... */)
{
    $last_args = array_slice(func_get_args(), 1);
    return function () use ($fun, $last_args) {
        return call_user_func_array(
            $fun,
            array_merge(func_get_args(), $last_args)
        );
    };
}

function not(callable $predicate)
{
    return function (...$args) use ($predicate) {
        return !call_user_func_array($predicate, $args);
    };
}

/**
 * Equivalent to second_fn(first_fn(...)).
 *
 * Yes, it is useful in some situations.
 */
function pipe(callable $first_fn, callable $second_fn)
{
    return function (...$args_first) use ($first_fn, $second_fn) {
        $first_result = $first_fn(...$args_first);
        return $second_fn($first_result);
    };
}

// @credits https://github.com/google/guava/issues/2834
function doubleToSortableLong(float $value)
{
    // Gymnastics to convert a float/double to a sortable long long
    $bits = unpack('J', pack('E', $value))[1];
    return $bits ^ (($bits >> (PHP_INT_SIZE * 8 - 1)) & PHP_INT_MAX);
}

/**
 * Generates a 128-bit / 16-byte random GUID. Under most circumstances
 * it has around 7 to 10 bytes of entropy over a medium timeframe.
 *
 * 8 bytes to encode the time of generation
 * 1 byte to encode machine identifier
 * 7 bytes of random generated numbers
 *
 * Note: GUIDs will create the $microtime input or the time it was called.
 */
function guid_generate(string $dbname = null, string $dbhost = null, float $microtime = null)
{
    if (!$microtime) {
        $microtime = \microtime(true);
    }

    $part_server_name = getHostName() ?: 'this machine';
    $machine_identifier = sprintf('%s-%s-%s', $part_server_name, $dbhost, $dbname);

    $wanted_length = 16;
    $part_time = pack('J', doubleToSortableLong($microtime));                 // 8 bytes
    $part_machine = substr(hash('fnv132', $machine_identifier, true), 0, 1);  // 1 byte
    $part_random = \random_bytes($wanted_length - strlen($part_time . $part_machine));
    return Base62::encode($part_time . $part_machine . $part_random);
}
