<?php

$column_indexes = [
    0x00 => [ 'name' => '00', 'render' => 'p--', 'value' => 0x00, ],
    0x01 => [ 'name' => '01', 'render' => 'p-1', 'value' => 0x01, ],
    0x02 => [ 'name' => '02', 'render' => 'p-2', 'value' => 0x02, ],
    0x03 => [ 'name' => '03', 'render' => 'p-3', 'value' => 0x03, ],
    0x04 => [ 'name' => '04', 'render' => 'p-4', 'value' => 0x04, ],
    0x05 => [ 'name' => '05', 'render' => 'p-5', 'value' => 0x05, ],
    0x06 => [ 'name' => '06', 'render' => 'p-6', 'value' => 0x06, ],
    0x07 => [ 'name' => '07', 'render' => 'p-7', 'value' => 0x07, ],
    0x08 => [ 'name' => '08', 'render' => 'p-8', 'value' => 0x08, ],
    0x09 => [ 'name' => '09', 'render' => 'p-9', 'value' => 0x09, ],
    0x0A => [ 'name' => '0A', 'render' => 'p10', 'value' => 0x0A, ],
    0x0B => [ 'name' => '0B', 'render' => 'p11', 'value' => 0x0B, ],
    0x0C => [ 'name' => '0C', 'render' => 'p12', 'value' => 0x0C, ],
    0x0D => [ 'name' => '0D', 'render' => 'p13', 'value' => 0x0D, ],
    0x0E => [ 'name' => '0E', 'render' => 'p14', 'value' => 0x0E, ],
    0x0F => [ 'name' => '0F', 'render' => 'p15', 'value' => 0x0F, ],
    0x10 => [ 'name' => '10', 'render' => 'p16', 'value' => 0x10, ],
    0x11 => [ 'name' => '11', 'render' => 'p17', 'value' => 0x11, ],
    0x1D => [ 'name' => '1D', 'render' => 'x1D', 'value' => 0x1D, ],
    0x1E => [ 'name' => '1E', 'render' => 'x1E', 'value' => 0x1E, ],
    0x1F => [ 'name' => '1F', 'render' => 'x1F', 'value' => 0x1F, ],
    0x31 => [ 'name' => '31', 'render' => 'x37', 'value' => 0x31, ],
    0x37 => [ 'name' => '37', 'render' => 'x55', 'value' => 0x37, ],
];

$beat_types = [
    0x00 => [ 'name' => 'Tap', 'render' => 'X', ],
    0x0B => [ 'name' => 'Slide #01 Start', 'render' => '<', ],
    0x0C => [ 'name' => 'Slide #01 Continue', 'render' => '>', ],
    0x15 => [ 'name' => 'Slide #02 Start', 'render' => '#', ],
    0x16 => [ 'name' => 'Slide #02 Continue', 'render' => '?', ],
    0xE8 => [ 'name' => 'Slide #03 Start', 'render' => '%', ],
    0xE9 => [ 'name' => 'Slide #03 Continue', 'render' => '&', ],
];

$vertical_offsets = [ // right before the (next) counter
    0x00 => [ 'name' => '00', 'render' => '- --', ],
    0x10 => [ 'name' => '10', 'render' => 'X 10', ],
    0x80 => [ 'name' => '80', 'render' => 'X 80', ],
    0x40 => [ 'name' => '40', 'render' => 'X 40', ],
    0xC0 => [ 'name' => 'C0', 'render' => 'X C0', ],
    0x20 => [ 'name' => '20', 'render' => 'X 20', ],
    0xA0 => [ 'name' => 'A0', 'render' => 'X A0', ],
    0x55 => [ 'name' => '55', 'render' => 'X 55', ],
    0x18 => [ 'name' => '18', 'render' => 'X 18', ],
    0xAA => [ 'name' => 'AA', 'render' => 'X AA', ],
    0x78 => [ 'name' => '78', 'render' => 'X 78', ],
    0xF8 => [ 'name' => 'F8', 'render' => 'X F8', ],
    0x28 => [ 'name' => '28', 'render' => 'X 28', ],
    0xB0 => [ 'name' => 'B0', 'render' => 'X B0', ],
    0xC1 => [ 'name' => 'C1', 'render' => 'X C1', ],
    0x16 => [ 'name' => '16', 'render' => 'X 16', ],
    0x45 => [ 'name' => '45', 'render' => 'X 45', ],
    0xE8 => [ 'name' => 'E8', 'render' => 'X E8', ],
    0xED => [ 'name' => 'ED', 'render' => 'X ED', ],
    0xF5 => [ 'name' => 'F5', 'render' => 'X F5', ],
    0x6C => [ 'name' => '6C', 'render' => 'X 6C', ],
    0xC5 => [ 'name' => 'C5', 'render' => 'X C5', ],
    0x4B => [ 'name' => '4B', 'render' => 'X 4B', ],
    0x50 => [ 'name' => '50', 'render' => 'X 50', ],
    0xD0 => [ 'name' => 'D0', 'render' => 'X D0', ],
    0xD8 => [ 'name' => 'D8', 'render' => 'X D8', ],
    0x15 => [ 'name' => '15', 'render' => 'X 15', ],
    0x58 => [ 'name' => '58', 'render' => 'X 58', ],
    0x70 => [ 'name' => '70', 'render' => 'X 70', ],
    0x90 => [ 'name' => '90', 'render' => 'X 90', ],
    0x98 => [ 'name' => '98', 'render' => 'X 98', ],
    0xD5 => [ 'name' => 'D5', 'render' => 'X D5', ],
    0xE0 => [ 'name' => 'E0', 'render' => 'X E0', ],
    0xF0 => [ 'name' => 'F0', 'render' => 'X F0', ],
    0x30 => [ 'name' => '30', 'render' => 'X 30', ],
    0x60 => [ 'name' => '60', 'render' => 'X 60', ],
    0x95 => [ 'name' => '95', 'render' => 'X 95', ],
    0x76 => [ 'name' => '76', 'render' => 'X 76', ],
    0x2A => [ 'name' => '2A', 'render' => 'X 2A', ],
    0x54 => [ 'name' => '54', 'render' => 'X 54', ],
    0x6B => [ 'name' => '6B', 'render' => 'X 6B', ],
    0xCD => [ 'name' => 'CD', 'render' => 'X CD', ],
    0xC8 => [ 'name' => 'C8', 'render' => 'X C8', ],
    0x4D => [ 'name' => '4D', 'render' => 'X 4D', ],
    0xB5 => [ 'name' => 'B5', 'render' => 'X B5', ],
    0xE5 => [ 'name' => 'E5', 'render' => 'X E5', ],
    0xD4 => [ 'name' => 'D4', 'render' => 'X D4', ],
    0x75 => [ 'name' => '75', 'render' => 'X 75', ],
    0x81 => [ 'name' => '81', 'render' => 'X 81', ],
    0x6D => [ 'name' => '6D', 'render' => 'X 6D', ],
    0xCA => [ 'name' => 'CA', 'render' => 'X CA', ],
    0x56 => [ 'name' => '56', 'render' => 'X 56', ],
    0x6A => [ 'name' => '6A', 'render' => 'X 6A', ],
    0xD6 => [ 'name' => 'D6', 'render' => 'X D6', ],
    0xAB => [ 'name' => 'AB', 'render' => 'X AB', ],
    0x01 => [ 'name' => '01', 'render' => 'X 01', ],
    0x19 => [ 'name' => '19', 'render' => 'X 19', ],
    0x99 => [ 'name' => '99', 'render' => 'X 99', ],
    0xEE => [ 'name' => 'EE', 'render' => 'X EE', ],
    0xAC => [ 'name' => 'AC', 'render' => 'X AC', ],
    0xFE => [ 'name' => 'FE', 'render' => 'X FE', ],
    0xFF => [ 'name' => 'FF', 'render' => 'X FF', ],
    0x7E => [ 'name' => '7E', 'render' => 'X 7E', ],
    0x41 => [ 'name' => '41', 'render' => 'X 41', ],
    0x7F => [ 'name' => '7F', 'render' => 'X 7F', ],
    0xA9 => [ 'name' => 'A9', 'render' => 'X A9', ],
    0xBF => [ 'name' => 'BF', 'render' => 'X BF', ],
    0xCB => [ 'name' => 'CB', 'render' => 'X CB', ],
    0xB6 => [ 'name' => 'B6', 'render' => 'X B6', ],
    0x96 => [ 'name' => '96', 'render' => 'X 96', ],
    0x35 => [ 'name' => '35', 'render' => 'X 35', ],
    0x8A => [ 'name' => '8A', 'render' => 'X 8A', ],
    0x2B => [ 'name' => '2B', 'render' => 'X 2B', ],
    0xEA => [ 'name' => 'EA', 'render' => 'X EA', ],
    0x29 => [ 'name' => '29', 'render' => 'X 29', ],
    0x3F => [ 'name' => '3F', 'render' => 'X 3F', ],
    0x82 => [ 'name' => '82', 'render' => 'X 82', ],
    0xDA => [ 'name' => 'DA', 'render' => 'X DA', ],
    0x85 => [ 'name' => '85', 'render' => 'X 85', ],
    0x88 => [ 'name' => '88', 'render' => 'X 88', ],
    0x0A => [ 'name' => '0A', 'render' => 'X 0A', ],
    0x08 => [ 'name' => '08', 'render' => 'X 08', ],
    0x8B => [ 'name' => '8B', 'render' => 'X 8B', ],
    0x48 => [ 'name' => '48', 'render' => 'X 48', ],
    0x38 => [ 'name' => '38', 'render' => 'X 38', ],
    0xB8 => [ 'name' => 'B8', 'render' => 'X B8', ],
    0x68 => [ 'name' => '68', 'render' => 'X 68', ],
    0x2C => [ 'name' => '2C', 'render' => 'X 2C', ],
    0x2D => [ 'name' => '2D', 'render' => 'X 2D', ],
    0xA8 => [ 'name' => 'A8', 'render' => 'X A8', ],
];

function decbin2($number, $char = '\'') {
    return str_pad(str_replace('0', $char, decbin($number)), 8, $char, STR_PAD_LEFT);
}

function format_thingies($number) {
    return sprintf(
        '0x%02.s <%s>',
        strtoupper(dechex($number)),
        decbin2($number)
    );
}

function format_thingies_hex($number) {
    return sprintf(
        '0x%02.s',
        strtoupper(dechex($number))
    );
}

function format_thingies_many(array $numbers) {
    $numbers = array_unique($numbers);
    natsort($numbers);
    $numbers = array_map(function ($number) { return sprintf('%s (%s)', $number, format_thingies_hex($number)); }, $numbers);
    return sprintf('[%sx] %s', count($numbers), implode(', ', $numbers));
}

