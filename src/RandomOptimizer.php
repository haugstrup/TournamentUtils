<?php

namespace haugstrup\TournamentUtils;

require_once 'Base.php';

class RandomOptimizer extends Base
{
    public $iterations = 1000;

    // Implement this in your subclass
    public function cost($solution)
    {
        return null;
    }

    // Implement this in your subclass
    public function solution($input)
    {
        return null;
    }

    public function solve($input)
    {
        $best_cost = null;
        $best_solution = null;

        for ($i = 0; $i < $this->iterations; $i++) {

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

        return ['cost' => $best_cost, 'solution' => $best_solution];
    }
}
