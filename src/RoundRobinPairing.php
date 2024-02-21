<?php namespace haugstrup\TournamentUtils;

require_once 'Base.php';

class RoundRobinPairing extends Base {

  public $players = array();
  public $double = false;

  public function __construct($players, $double = false) {
    $this->players = $players;
    $this->double = $double;
  }

  public function build() {
    $players = $this->players;
    $pairings = array();

    // Odd number of players, add dummy player
    $has_bye_player = false;
    if (count($players)%2 != 0) {
      array_unshift($players, null);
      $has_bye_player = true;
    }

    $split = array_chunk($players, count($players)/2);
    $top = $split[0];
    $bottom = array_reverse($split[1]);

    for($i=1;$i<=(count($players)-1);$i++) {
      $round = array('groups' => array(), 'byes' => array());
      foreach ($top as $index => $top_player) {
        if (is_null($top_player)) {
          $round['byes'][] = $bottom[$index];
        } else {
          $group = $index === 0 && $i % 2 ? array($bottom[$index], $top_player) : array($top_player, $bottom[$index]);
          $round['groups'][] = $group;

          // If double round robin, create two games
          if ($this->double) {
            $round['groups'][] = array_reverse($group);
          }
        }
      }
      $pairings[] = $round;

      // Rotate players
      $bottom[] = array_pop($top);
      array_splice($top, 1, 0, array_splice($bottom, 0, 1));
    }

    return $pairings;
  }

}
