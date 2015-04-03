<?php

require('../../src/SlaughterPairing.php');

// Prep list of players
$players_list = array();
for($i=0;$i<15;$i++) {
  $players_list[] = 'Seed #'.($i+1);
}

$builder = new haugstrup\TournamentUtils\SlaughterPairing($players_list);

$builder->group_size = 4;

$groups = $builder->build();

print_r($groups);
