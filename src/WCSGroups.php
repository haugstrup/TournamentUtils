<?php namespace haugstrup\TournamentUtils;

require_once 'Base.php';

class WCSGroups extends Base {

  public $players = array();
  public $group_size = array();

  public function __construct($players, $group_size) {
    $this->players = $players;
    $this->group_size = $group_size;
  }

  public function build() {
    $players = $this->players;
    $group_size = $this->group_size;
    $groups = array();

    if (!$players) return $groups;

    $groups_count = floor(count($players)/$group_size);
    if ($groups_count == 0) $groups_count = 1;

    $regulars_count = $group_size*$groups_count;

    $regulars = array_chunk(array_slice($players, 0, $regulars_count), $groups_count);
    $top_seeds = array_shift($regulars);
    $extras = array_slice($players, $regulars_count);

    foreach ($top_seeds as $player) {
      $group_players = array();
      foreach ($regulars as $j => $chunk) {
        $group_players[] = array_pop($regulars[$j]);
      }

      array_unshift($group_players, $player);

      $groups[] = $group_players;
    }

    // Place extras evenly, from the bottom
    if ($extras) {
      $groups = array_reverse($groups);
      $i = 0;
      foreach ($extras as $player) {
        $groups[$i][] = $player;
        $i++;
        if (!isset($groups[$i])) $i=0;
      }
      $groups = array_reverse($groups);
    }

    return $groups;
  }

}
