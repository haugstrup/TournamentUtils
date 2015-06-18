<?php
require('../../src/BalancedPairing.php');

$list = array(
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
);
$previously_matched = array(
  1 => array(9, 10, 11, 12, 13),
  2 => array(3, 4, 5, 6, 7),
  3 => array(2, 4, 5, 6, 7),
  4 => array(2, 3, 8, 13, 16),
  5 => array(2, 3, 6, 14, 15),
  6 => array(2, 3, 5, 14, 16),
  7 => array(2, 3, 8, 10, 12),
  8 => array(4, 7, 9, 12, 15),
  9 => array(1, 8, 10, 14, 15),
  10 => array(1, 7, 9, 11, 16),
  11 => array(1, 10, 11, 13, 14),
  12 => array(1, 7, 8, 12, 13),
  13 => array(1, 4, 11, 12, 15),
  14 => array(5, 6, 9, 11, 16),
  15 => array(5, 8, 9, 13, 16),
  16 => array(4, 6, 10, 14, 15),
);

$three_player_group_counts = array(
  1 => 1,
  2 => 1,
  3 => 1
);

$builder = new haugstrup\TournamentUtils\BalancedPairing($list, $previously_matched, 4, $three_player_group_counts);
$pairings = $builder->build();

print_r($pairings);
