<?php

require '../../src/BalancedPlayerOrder.php';

$players = [
    'Player#1',
    'Player#2',
    'Player#3',
    'Player#4',
];

$player_order_counts = [
    'Player#1' => [0 => 0, 1 => 2, 2 => 0, 3 => 1],
    'Player#2' => [0 => 1, 1 => 0, 2 => 0, 3 => 1],
    'Player#3' => [0 => 2, 1 => 2, 2 => 3, 3 => 2],
    'Player#4' => [0 => 1, 1 => 1, 2 => 5, 3 => 1],
];

$builder = new haugstrup\TournamentUtils\BalancedPlayerOrder($players, $player_order_counts);
$order = $builder->build();

print_r($order);
