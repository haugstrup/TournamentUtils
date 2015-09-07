<?php

require('../../src/RoundRobinPairing.php');

// Prep list of players
$players_list = array();
for($i=0;$i<15;$i++) {
  $players_list[] = 'Player #'.($i+1);
}

$builder = new haugstrup\TournamentUtils\RoundRobinPairing($players_list, true);

$groups = $builder->build();

print_r($groups);
