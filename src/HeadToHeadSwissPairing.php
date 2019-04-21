<?php namespace haugstrup\TournamentUtils;

require_once 'Base.php';

class HeadToHeadSwissPairing extends Base {

  public $groups = array();
  public $byes = array();

  /**
   * Pairs players with similar wins against each other. If
   * there are an odd number of players, this class gives
   * the bye to one of the players with the fewest number
   * of byes. It then pairs off remaining players
   * group-by-group. If there are an odd number of players
   * in a group, it will select a player from the next group
   * as the odd player's opponent.
   *
   * @param array $groups an array of associative arrays
   * with player ID as the key, and a value of an associative
   * array of opponent IDs as keys and match count as values.
   * Each array in $groups contains a pool of similarly-matched
   * players (e.g., players with same number of strikes) to
   * pair up for matches.
   * @param array $byes an associative array of player ID as
   * the key and the number of byes as the value. This class
   * is OK with $byes containing players that are not also in
   * $groups, and assumes players not present in $byes have zero byes.
   */
  public function __construct($groups, $byes = array()) {
    $this->groups = $groups;
    $this->byes = $byes;
  }

  public function build() {
    $pairings = array(); // array of 2-element arrays of player IDs
    $byes = array(); // array with ID of player with bye, or empty if no bye
    $past_matches_all = array();

    // Figure out if we need to pick a player for a bye.
    $player_count = 0;
    $player_byes = array(); # assoc. array of player_id => bye_count
    foreach ($this->groups as $group) {
      foreach ($group as $player_id => $opponents) {
        $player_count++;
        $player_byes[$player_id] = !empty($this->byes[$player_id]) ? $this->byes[$player_id] : 0;
        $past_matches_all[$player_id] = $opponents;
      }
    }
    // If there's an odd number of players, select one for the bye
    if ($player_count % 2) {
      // choose only from players with the fewest byes
      $bye_eligible = array_keys($player_byes, min($player_byes));
      $byes[] = $bye_eligible[array_rand($bye_eligible)];
    }

    $match = array(); // 2-element array of player IDs
    foreach ($this->groups as $group) {
      $pool = array_keys($group); // player IDs of current group

      // remove the "bye" player from the pool if they're in it
      if (count($byes) > 0 && $bye_in_pool = array_search($byes[0], $pool)) {
        unset($pool[$bye_in_pool]);
      }
      shuffle($pool);
      while (!empty($pool)) {
        if (empty($match)) {
          // select first player for match from shuffled pool
          $match[] = array_pop($pool);
        } else {
          // Select an opponent from those $match[0] has played the least
          $opponents = array(); // assoc. array of player_id => times_played
          $past_matches = $past_matches_all[$match[0]];
          foreach ($pool as $possible_opponent) {
            $opponents[$possible_opponent] = !empty($past_matches[$possible_opponent]) ? $past_matches[$possible_opponent] : 0;
          }
          // randomly select an opponent from those played least
          $best_matches = array_keys($opponents, min($opponents));
          $opponent_id = $best_matches[array_rand($best_matches)];
          $match[] = $opponent_id;

          // remove selected opponent from pool
          unset($pool[array_search($opponent_id, $pool)]);

          // group completed, create a new matching
          $pairings[] = $match;
          $match = array();
        }
      }
    }

    return array('groups' => $pairings, 'byes' => $byes);
  }

}
