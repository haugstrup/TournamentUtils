<?php
require('../../src/BalancedPlayerOrder.php');

$players = array(
  'Player#1',
  'Player#2',
  'Player#3',
  'Player#4',
);

$player_order_counts = array(
  'Player#1' => array(0 => 0, 1 => 2, 2 => 0, 3 => 1),
  'Player#2' => array(0 => 1, 1 => 0, 2 => 0, 3 => 1),
  'Player#3' => array(0 => 2, 1 => 2, 2 => 3, 3 => 2),
  'Player#4' => array(0 => 1, 1 => 1, 2 => 5, 3 => 1),
);

$builder = new haugstrup\TournamentUtils\BalancedPlayerOrder($players, $player_order_counts);
$order = $builder->build();

print_r($order);
