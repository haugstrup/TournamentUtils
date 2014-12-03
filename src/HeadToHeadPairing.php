<?php namespace haugstrup\TournamentUtils;

class HeadToHeadPairing {

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

      // For each player
      foreach ($group as $player_id => $opponents) {

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
            $random_opponent = $opponent_group[array_rand($opponent_group, 1)];

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

$builder = new HeadToHeadPairing($groups);
$pairings = $builder->build();

print_r($pairings);

