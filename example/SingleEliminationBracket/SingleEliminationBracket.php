<?php

require '../../src/SingleEliminationBracket.php';

// Check first round groups
$sizes = [2, 4, 8, 16, 32, 64, 128];
foreach ($sizes as $size) {
    $bracket = new haugstrup\TournamentUtils\SingleEliminationBracket($size, [], []);
    $groups = $bracket->first_round_groups();

    if (count($groups) !== $size / 2) {
        throw new Exception('Not correct amount of games for '.$size);
    }

    $seeds = [];
    foreach ($groups as $group) {

        if (array_sum($group) !== ($size + 1)) {
            throw new Exception('Matchup is wrong');
        }

        foreach ($group as $seed) {
            if (in_array($seed, $seeds)) {
                throw new Exception('Seed '.$seed.' used twice');
            }

            $seeds[] = $seed;
        }
    }

    if (count($seeds) !== $size) {
        throw new Exception('Not correct amount of seeds');
    }

}

// Prep list of players
$players_list = [];
for ($i = 0; $i <= 15; $i++) {
    $players_list[] = 'Seed #'.($i + 1);
}

// Prep wins. In the first round no one has won anything yet
$winners_by_heap_index = [];

$bracket = new haugstrup\TournamentUtils\SingleEliminationBracket(16, $players_list, $winners_by_heap_index);

echo 'Children for game #4: '.implode(', ', $bracket->children(4))."\n";
echo 'Parent for game #4: '.$bracket->parent(4)."\n";
echo 'Round for game #1: '.$bracket->round(1)."\n";
echo 'Round for game #4: '.$bracket->round(4)."\n";
echo 'Round for game #15: '.$bracket->round(15)."\n";
echo 'Number of rounds: '.$bracket->number_of_rounds()."\n";
echo 'Games in round #2: '.implode(', ', $bracket->indexes_in_round(2))."\n";

echo 'Opponents for game #14: '.implode(', ', $bracket->opponents_for_index(14))."\n";

// Add some wins so we can pick players from subsequent rounds

$bracket->winners = [8 => 'Seed #16', 9 => 'Seed #8'];
echo 'Opponents for game #4: '.implode(', ', $bracket->opponents_for_index(4))."\n";

echo "\n\n";
