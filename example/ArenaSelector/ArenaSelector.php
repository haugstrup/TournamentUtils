<?php
require('../../src/ArenaSelector.php');

// TODO: Make this into unit tests

// Sample classes
class Arena {
  public function __construct($id, $count = 0) {
    $this->id = $id;
    $this->count = $count;
  }
  public function getArenaId() {
    return $this->id;
  }
  public function getArenaCount() {
    return $this->count;
  }
}

// Prep list of counts
$arena_counts = array('Pinbot' => 1, 'Paragon' => 4, 'Embryon' => 4, 'Rollergames' => 1);

// Prep list of available arenas
$available_arenas = array(new Arena('Pinbot'), new Arena('Rollergames'), new Arena('Paragon'), new Arena('Embryon'), new Arena('Scorpion'), new Arena('Black Pyramid'));

// Init and run selector
$selector = new haugstrup\TournamentUtils\ArenaSelector($arena_counts, $available_arenas);
$arena = $selector->select();

// Should pick Scorpion or Black Pyramid since they are unplayed by all
var_dump($arena);

// ###########################################################################

// Prep list of available arenas
$available_arenas = array(new Arena('Pinbot'), new Arena('Rollergames'), new Arena('Paragon'), new Arena('Embryon'));

// Init and run selector
$selector = new haugstrup\TournamentUtils\ArenaSelector($arena_counts, $available_arenas);
$arena = $selector->select();

// Should pick Pinbot or Rollergames since it they have lowest play count
var_dump($arena);
