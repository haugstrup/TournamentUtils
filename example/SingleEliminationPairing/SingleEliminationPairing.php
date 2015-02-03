<?php

require('../../src/SingleEliminationPairing.php');

// Prep list of players
$players_list = array();
for($i=0;$i<=15;$i++) {
  $players_list[] = 'Seed #'.($i+1);
}

// Prep wins. In the first round no one has won anything yet
$wins = array();
for($i=0;$i<15;$i++) {
  $wins['Seed #'.($i+1)] = 0;
}

$builder = new haugstrup\TournamentUtils\SingleEliminationPairing($players_list, $wins);
$groups = $builder->build(0);
print_r($groups);

// Second round, we need some wins -- let all the low seeds win
$wins['Seed #9'] = 1;
$wins['Seed #10'] = 1;
$wins['Seed #11'] = 1;
$wins['Seed #12'] = 1;
$wins['Seed #13'] = 1;
$wins['Seed #14'] = 1;
$wins['Seed #15'] = 1;
$wins['Seed #16'] = 1;

$builder = new haugstrup\TournamentUtils\SingleEliminationPairing($players_list, $wins);
$groups = $builder->build(1);
print_r($groups);

// And do it all for 14 players to show byes

// Prep list of players
$players_list = array();
for($i=0;$i<=13;$i++) {
  $players_list[] = 'Seed #'.($i+1);
}

// Prep wins. In the first round no one has won anything yet
$wins = array();
for($i=0;$i<13;$i++) {
  $wins['Seed #'.($i+1)] = 0;
}

$builder = new haugstrup\TournamentUtils\SingleEliminationPairing($players_list, $wins);
$groups = $builder->build(0);
print_r($groups);

// Second round, we need some wins -- let all the low seeds win
$wins['Seed #9'] = 1;
$wins['Seed #10'] = 1;
$wins['Seed #11'] = 1;
$wins['Seed #12'] = 1;
$wins['Seed #13'] = 1;
$wins['Seed #14'] = 1;

$builder = new haugstrup\TournamentUtils\SingleEliminationPairing($players_list, $wins);
$groups = $builder->build(1);
print_r($groups);
