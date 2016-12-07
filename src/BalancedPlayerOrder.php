<?php namespace haugstrup\TournamentUtils;

require_once 'RandomOptimizer.php';

class BalancedPlayerOrder extends RandomOptimizer {
  public $players = array();
  public $player_order_counts = array();

  public function __construct($players, $player_order_counts = array()) {
    $this->players = $players;
    $this->player_order_counts = $player_order_counts;
  }

  public function solution($input) {
    $solution = array();

    // Give each player a random position
    $positions = array(0, 1, 2, 3);
    $positions = array_slice($positions, 0, count($input['players']));
    $this->shuffle($positions);

    foreach ($input['players'] as $player) {
      $solution[$player] = array_pop($positions);
    }

    return $solution;
  }

  public function cost($solution) {
    // Cost is a function of how many times a player has played that position
    $cost = 0;

    foreach ($solution as $player => $pos) {
      if (isset($this->player_order_counts[$player]) && isset($this->player_order_counts[$player][$pos])) {
        $cost = $cost + pow($this->player_order_counts[$player][$pos], 2);
      }
    }

    return $cost;
  }

  public function build() {
    $result = $this->solve(array('players' => $this->players));
    return array('cost' => $result['cost'], 'order' => $result['solution']);
  }

}
