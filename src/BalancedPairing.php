<?php namespace haugstrup\TournamentUtils;

class BalancedPairing {

  public $group_size = 2;
  public $list = array();
  public $previously_matched = array();
  public $iterations = 1000;

  public function __construct($list, $previously_matched = array(), $group_size = 2) {
    $this->list = $list;
    $this->previously_matched = $previously_matched;
    $this->group_size = $group_size;

    if ($group_size !== 2 && $group_size !== 4) {
      throw new \Exception('Group size must be 2 or 4');
    }
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

    for($i=0;$i<$this->iterations;$i++) {
      $available = $this->list;
      $solution = array();

      while (count($available) > 0) {


        if ($this->group_size === 4 && count($available) < 10 && count($available)%4 != 0 && count($available) !== 7) {
          $matchup = array_rand($available, 3);
        } else if (count($available) === 1) {
          $matchup = array_keys($available);
        } else {
          $matchup = array_rand($available, $this->group_size);
        }

        $solution[] = $matchup;
        foreach ($matchup as $id) {
          unset($available[$id]);
        }
      }

      $cost = $this->cost($solution);

      if ($best === null || $cost < $best) {
        $best = $cost;

        $groups = array();
        foreach ($solution as $matchup) {
          $group = array();

          foreach ($matchup as $id) {
            $group[] = $this->list[$id];
          }

          $groups[] = $group;
        }

        $best_solution = $solution;
        $best_groups = $groups;


        if ($cost == 0) {
          break;
        }
      }
    }

    return array('cost' => $best, 'groups' => $best_groups);
  }

}
