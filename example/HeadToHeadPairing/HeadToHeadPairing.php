<?php
require('../../src/HeadToHeadPairing.php');

// TODO: Make this into unit tests

$groups = array(
  array(
    'Andreas' => array('Per' => 1, 'Darren' => 1),
    'Per' => array('Matt' => 1, 'Andreas' => 1),
    'Shon' => array('Sally' => 1, 'Eric' => 1)
  ),
  array(
    'Sally' => array('Darren' => 1, 'Shon' => 1),
    'Darren' => array('Andreas' => 1, 'Sally' => 1),
    'Matt' => array('Per' => 1, 'Eric' => 1),
    'Eric' => array('Shon' => 1, 'Matt' => 1)
  )
);

$builder = new haugstrup\TournamentUtils\HeadToHeadPairing($groups);
$pairings = $builder->build();

print_r($pairings);
