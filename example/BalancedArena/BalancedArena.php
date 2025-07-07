<?php

require '../../src/BalancedArena.php';

$groups = [
    ['Player#1', 'Player#5'],
    ['Player#2', 'Player#6'],
    ['Player#3', 'Player#7'],
    ['Player#4', 'Player#8'],
];

$available_arenas = ['Pinbot', 'Paragon', 'Embryon', 'Rollergames', 'Scorpion', 'Black Pyramid'];

$arena_plays = [
    'Player#1' => ['Pinbot' => 2, 'Rollergames' => 3, 'Scorpion' => 2],
    'Player#3' => ['Paragon' => 2, 'Embryon' => 3, 'Black Pyramid' => 2],
    'Player#3' => ['Pinbot' => 2, 'Rollergames' => 2, 'Black Pyramid' => 2, 'Embryon' => 2],
    'Player#4' => ['Pinbot' => 3, 'Rollergames' => 2, 'Scorpion' => 2],
    'Player#5' => ['Pinbot' => 2, 'Rollergames' => 3, 'Black Pyramid' => 2],
    'Player#6' => ['Paragon' => 2, 'Embryon' => 3, 'Scorpion' => 2],
    'Player#7' => ['Pinbot' => 2, 'Pinbot' => 3, 'Scorpion' => 2],
    'Player#8' => ['Pinbot' => 2, 'Black Pyramid' => 3, 'Rollergames' => 2],
];

$builder = new haugstrup\TournamentUtils\BalancedArena($groups, $available_arenas, 3, $arena_plays);
$arenas = $builder->build();

print_r($arenas);
