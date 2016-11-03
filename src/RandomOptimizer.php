<?php namespace haugstrup\TournamentUtils;

class RandomOptimizer {
  public $iterations = 1000;

  // Allows array_rand and shuffle to be stubbed out in testing.
  public function array_rand($array, $number = 1) {
    return array_rand($array, $number);
  }

  public function shuffle($array) {
    shuffle($array);
    return $array;
  }

  // Implement this in your subclass
  public function cost($solution) {
    return null;
  }

  // Implement this in your subclass
  public function solution($input) {
    return null;
  }

  public function solve($input) {
    $best_cost = null;
    $best_solution = null;

    for($i=0;$i<$this->iterations;$i++) {

      $solution = $this->solution($input);
      $cost = $this->cost($solution);

      if (is_null($best_cost) || $cost < $best_cost) {
        $best_cost = $cost;
        $best_solution = $solution;

        if ($cost == 0) {
          break;
        }
      }
    }

    return array('cost' => $best_cost, 'solution' => $best_solution);
  }

}
