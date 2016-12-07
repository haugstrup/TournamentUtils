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
  'Player#1' => array('Pinbot' => 2, 'Rollergames' => 3, 'Scorpion' => 2),
  'Player#3' => array('Paragon' => 2, 'Embryon' => 3, 'Black Pyramid' => 2),
  'Player#3' => array('Pinbot' => 2, 'Rollergames' => 2, 'Black Pyramid' => 2, 'Embryon' => 2),
  'Player#4' => array('Pinbot' => 3, 'Rollergames' => 2, 'Scorpion' => 2),
  'Player#5' => array('Pinbot' => 2, 'Rollergames' => 3, 'Black Pyramid' => 2),
  'Player#6' => array('Paragon' => 2, 'Embryon' => 3, 'Scorpion' => 2),
  'Player#7' => array('Pinbot' => 2, 'Pinbot' => 3, 'Scorpion' => 2),
  'Player#8' => array('Pinbot' => 2, 'Black Pyramid' => 3, 'Rollergames' => 2),
);

$builder = new haugstrup\TournamentUtils\BalancedArena($groups, $available_arenas, 3, $arena_plays);
$arenas = $builder->build();

print_r($arenas);
