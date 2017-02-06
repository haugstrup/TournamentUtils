<?php namespace haugstrup\TournamentUtils;

require_once 'RandomOptimizer.php';

class BalancedPairing extends RandomOptimizer {

  public $group_size = 2;
  public $list = array();
  public $previously_matched = array();
  public $three_player_group_counts = array();

  public function __construct($list, $previously_matched = array(), $group_size = 2, $three_player_group_counts = array()) {
    $this->list = $list;
    $this->previously_matched = $previously_matched;
    $this->three_player_group_counts = $three_player_group_counts;
    $this->group_size = $group_size;

    if ($group_size !== 2 && $group_size !== 4) {
      throw new \Exception('Group size must be 2 or 4');
    }
  }

  public function solution($input) {
    $solution = array();

    while (count($input) > 0) {

      if (count($input) === 1 || count($input) === 2) {
        $matchup = array_keys($input);
      } elseif ($this->group_size === 4 && count($input) < 10 && count($input)%4 !== 0 && count($input) !== 7) {
        $matchup = $this->array_rand($input, 3);
      } else {
        $matchup = $this->array_rand($input, $this->group_size);
      }

      $solution[] = $matchup;
      foreach ($matchup as $id) {
        unset($input[$id]);
      }
    }

    return $solution;
  }

  public function cost($solution) {
    $cost = 0;

    foreach ($solution as $matchup) {

      foreach ($matchup as $id) {

        // Add to cost if:
        // * Group size is 4
        // * Current matchup has less than 4 players
        // * Player has previously played in a three player group
        if ($this->group_size === 4 && count($matchup) < 4 && isset($this->three_player_group_counts[$id])) {
          $cost = $cost+pow($this->three_player_group_counts[$id]+12, 2);
        }

        // Add to cost if players have been matched against each other previously
        foreach ($matchup as $match) {
          if ($id !== $match && isset($this->previously_matched[$id])) {
            $added_cost = 0;
            foreach ($this->previously_matched[$id] as $c) {
              if ($c === $match) {
                $added_cost++;
              }
            }
            $cost = $cost+pow($added_cost, 2);
          }

        }

      }
    }

    return $cost;
  }

  public function build() {
    $result = $this->solve($this->list);

    $groups = array();
    foreach ($result['solution'] as $matchup) {
      $group = array();

      foreach ($matchup as $id) {
        $group[] = $this->list[$id];
      }

      $groups[] = $group;
    }

    return array('cost' => $result['cost'], 'groups' => $groups);
  }

}
