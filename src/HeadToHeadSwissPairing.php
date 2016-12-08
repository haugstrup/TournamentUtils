<?php namespace haugstrup\TournamentUtils;

require_once 'Base.php';

class HeadToHeadSwissPairing extends Base {

  public $groups = array();
  public $byes = array();

  public function __construct($groups, $byes = array()) {
    $this->groups = $groups;
    $this->byes = asort($byes);
  }

  public function build() {
    $pairings = array();
    $byes = array();
    $matched_players = array();
    $groups = $this->groups;

    // Loop through each sub group
    for($index=0;$index<count($groups);$index++) {
      $group = $groups[$index];
      // If there are only two players, they play each other
      if (count($group) == 2) {
        $keys = array_keys($group);
        $pairings[] = $keys;
        $matched_players[] = $keys[0];
        $matched_players[] = $keys[1];
        continue;
      }

      $player_ids = array_keys($group);
      $player_ids = $this->shuffle($player_ids);

      // For each player
      foreach ($player_ids as $player_id) {
        $opponents = $group[$player_id];

        if (in_array($player_id, $matched_players)) {
          continue;
        }

        // Build list of available opponents, grouped by number of times played
        $available_opponents = array();
        foreach ($group as $opponent_id => $value) {
          if (!in_array($opponent_id, $matched_players) && $opponent_id != $player_id) {
            $key = isset($opponents[$opponent_id]) ? $opponents[$opponent_id] : 0;
            $available_opponents[$key][] = $opponent_id;
          }
        }
        ksort($available_opponents);

        // Go through opponent groups, pick first available player
        foreach ($available_opponents as $opponent_group) {
          if (count($opponent_group) > 0) {
            $random_opponent = $opponent_group[$this->array_rand($opponent_group, 1)];

            $pairings[] = array($player_id, $random_opponent);
            $matched_players[] = $player_id;
            $matched_players[] = $random_opponent;
            break;
          }
        }

        // If there are no available opponents, player is moved into the next sub-group
        if (!in_array($player_id, $matched_players)) {
          $key = $index+1;
          if (array_key_exists($key, $groups)) {
            $groups[$key] = array($player_id => $opponents)+$groups[$key];
          }
          else {
            $byes[] = $player_id;
            $matched_players[] = $player_id;
          }
        }
      }

    }

    return array('groups' => $pairings, 'byes' => $byes);
  }

}
