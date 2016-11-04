<?php namespace haugstrup\TournamentUtils;

require_once 'Base.php';

class ArenaSelector extends Base {

  public $arena_counts = array();
  public $available_arenas = array();

  public function __construct($arena_counts, $available_arenas) {
    $this->arena_counts = $arena_counts;
    $this->available_arenas = $available_arenas;
  }

  public function select() {

    asort($this->arena_counts);

    $arenas_by_count = array();
    foreach ($this->arena_counts as $arena_id => $count) {
      if (!isset($arenas_by_count[$count])) {
        $arenas_by_count[$count] = array();
      }
      $arenas_by_count[$count][] = $arena_id;
    }

    // Look for arenas no player has played
    $unplayed = array();
    foreach ($this->available_arenas as $arena) {
      if (!isset($this->arena_counts[$arena->getArenaId()])) {
        $unplayed[] = $arena;
      }
    }
    if ($unplayed) {
      return $unplayed[$this->array_rand($unplayed, 1)];
    }

    // Look through played arenas to see if any are available
    foreach ($arenas_by_count as $list) {

      // Get all available in the current count list
      $available = array();
      foreach ($list as $arena_id) {
        foreach ($this->available_arenas as $arena) {
          if ($arena_id == $arena->getArenaId()) {
            $available[] = $arena;
          }
        }
      }
      if ($available) {
        return $available[$this->array_rand($available, 1)];
      }
    }

    // Can't find arena
    return null;

  }

}
