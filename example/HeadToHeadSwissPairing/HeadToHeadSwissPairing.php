<?php

require '../../src/HeadToHeadSwissPairing.php';

// TODO: Make this into unit tests

$groups = [
    [
        'Andreas' => ['Per' => 1, 'Darren' => 1],
        'Per' => ['Matt' => 1, 'Andreas' => 1],
        'Shon' => ['Sally' => 1, 'Eric' => 1],
    ],
    [
        'Sally' => ['Darren' => 1, 'Shon' => 1],
        'Darren' => ['Andreas' => 1, 'Sally' => 1],
        'Matt' => ['Per' => 1, 'Eric' => 1],
        'Eric' => ['Shon' => 1, 'Matt' => 1],
    ],
];

$builder = new haugstrup\TournamentUtils\HeadToHeadSwissPairing($groups);
$pairings = $builder->build();

print_r($pairings);
