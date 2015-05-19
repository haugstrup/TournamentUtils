<?php namespace haugstrup\TournamentUtils;

require_once 'RandomOptimizer.php';

class BalancedPairing extends RandomOptimizer {

  public $group_size = 2;
  public $list = array();
  public $previously_matched = array();

  public function __construct($list, $previously_matched = array(), $group_size = 2) {
    $this->list = $list;
    $this->previously_matched = $previously_matched;
    $this->group_size = $group_size;

    if ($group_size !== 2 && $group_size !== 4) {
      throw new \Exception('Group size must be 2 or 4');
    }
  }

  public function solution($input) {
    $solution = array();

    while (count($input) > 0) {
      if ($this->group_size === 4 && count($input) < 10 && count($input)%4 != 0 && count($input) !== 7) {
        $matchup = array_rand($input, 3);
      } else if (count($input) === 1) {
        $matchup = array_keys($input);
      } else {
        $matchup = array_rand($input, $this->group_size);
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


    // TODO: If the cost could go up when a person who has played a three player group is assigned to another three player group that would be great.


    foreach ($solution as $matchup) {
      foreach ($matchup as $id) {

        foreach ($matchup as $match) {
          if ($id !== $match && isset($this->previously_matched[$id])) {

            foreach ($this->previously_matched[$id] as $c) {
              if ($c === $match) {
                $cost++;
              }
            }

          }
        }

      }
    }

    return $cost;
  }

  public function build() {
    $best = null;
    $best_solution = null;
    $best_groups = null;

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
