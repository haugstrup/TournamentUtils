<?php namespace haugstrup\TournamentUtils;

class RandomOptimizer {
  public $iterations = 1000;

  // Implement this in your subclass
  public function cost($solution) {
    return null;
  }

  // Implement this in your subclass
  public function solution($input) {
    return null;
  }

  public function solve($input) {
    $best = null;
    $best_solution = null;

    for($i=0;$i<$this->iterations;$i++) {

      $solution = $this->solution($input);
      $cost = $this->cost($solution);

      if ($best === null || $cost < $best) {
        $best = $cost;
        $best_solution = $solution;

        if ($cost == 0) {
          break;
        }
      }
    }

    return array('cost' => $best, 'solution' => $solution);
  }

}
