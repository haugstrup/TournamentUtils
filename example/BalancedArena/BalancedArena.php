<?php
require('../../src/BalancedArena.php');

$groups = array(
  array('Player#1', 'Player#5'),
  array('Player#2', 'Player#6'),
  array('Player#3', 'Player#7'),
  array('Player#4', 'Player#8'),
);

$available_arenas = array('Pinbot', 'Paragon', 'Embryon', 'Rollergames', 'Scorpion', 'Black Pyramid');

$arena_plays = array(
  'Player#1' => array('Pinbot' => 1, 'Rollergames' => 2, 'Scorpion' => 1),
  'Player#2' => array('Paragon' => 1, 'Embryon' => 2, 'Black Pyramid' => 1),
  'Player#3' => array('Pinbot' => 1, 'Rollergames' => 1, 'Black Pyramid' => 1, 'Embryon' => 1),
  'Player#4' => array('Pinbot' => 2, 'Rollergames' => 1, 'Scorpion' => 1),
  'Player#5' => array('Pinbot' => 1, 'Rollergames' => 2, 'Black Pyramid' => 1),
  'Player#6' => array('Paragon' => 1, 'Embryon' => 2, 'Scorpion' => 1),
  'Player#7' => array('Pinbot' => 1, 'Pinbot' => 2, 'Scorpion' => 1),
  'Player#8' => array('Pinbot' => 1, 'Black Pyramid' => 2, 'Rollergames' => 1),
);

$builder = new haugstrup\TournamentUtils\BalancedArena($groups, $available_arenas, 3, $arena_plays);
$arenas = $builder->build();

print_r($arenas);
