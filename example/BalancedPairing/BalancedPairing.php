<?php

require '../../src/BalancedPairing.php';

$list = [
    1 => 'Player#1',
    2 => 'Player#2',
    3 => 'Player#3',
    4 => 'Player#4',
    5 => 'Player#5',
    6 => 'Player#6',
    7 => 'Player#7',
    8 => 'Player#8',
    9 => 'Player#9',
    10 => 'Player#10',
    11 => 'Player#11',
    12 => 'Player#12',
    13 => 'Player#13',
    14 => 'Player#14',
    15 => 'Player#15',
    // 16 => 'Player#16',
];
$previously_matched = [
    1 => [9, 10, 11, 12, 13, 2, 3, 4, 5, 6],
    2 => [3, 4, 5, 6, 7, 1, 3, 4, 5, 6],
    3 => [2, 4, 5, 6, 7, 1],
    4 => [2, 3, 8, 13, 16, 1, 2, 3, 4, 5, 6],
    5 => [2, 3, 6, 14, 15, 1, 4, 5, 6, 7, 8],
    6 => [2, 3, 5, 14, 16],
    7 => [2, 3, 8, 10, 12],
    8 => [4, 7, 9, 12, 15],
    9 => [1, 8, 10, 14, 15],
    10 => [1, 7, 9, 11, 16],
    11 => [1, 10, 11, 13, 14],
    12 => [1, 7, 8, 12, 13],
    13 => [1, 4, 11, 12, 15],
    14 => [5, 6, 9, 11, 16],
    15 => [5, 8, 9, 13, 16],
    16 => [4, 6, 10, 14, 15],
];

$three_player_group_counts = [
    1 => 3,
    2 => 2,
    3 => 2,
];

$builder = new haugstrup\TournamentUtils\BalancedPairing($list, $previously_matched, 4, $three_player_group_counts);
$pairings = $builder->build();

print_r($pairings);
