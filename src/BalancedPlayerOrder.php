<?php

namespace haugstrup\TournamentUtils;

require_once 'RandomOptimizer.php';

class BalancedPlayerOrder extends RandomOptimizer
{
    public $players = [];

    public $player_order_counts = [];

    public function __construct($players, $player_order_counts = [])
    {
        $this->players = $players;
        $this->player_order_counts = $player_order_counts;
    }

    public function solution($input)
    {
        $solution = [];

        // Give each player a random position
        $positions = [0, 1, 2, 3];
        $positions = array_slice($positions, 0, count($input['players']));
        $positions = $this->shuffle($positions);

        foreach ($input['players'] as $player) {
            $solution[$player] = array_pop($positions);
        }

        return $solution;
    }

    public function cost($solution)
    {
        // Cost is a function of how many times a player has played that position
        $cost = 0;

        foreach ($solution as $player => $pos) {
            if (isset($this->player_order_counts[$player]) && isset($this->player_order_counts[$player][$pos])) {
                $cost = $cost + pow($this->player_order_counts[$player][$pos], 2);
            }
        }

        return $cost;
    }

    public function build()
    {
        $result = $this->solve(['players' => $this->players]);

        return ['cost' => $result['cost'], 'order' => $result['solution']];
    }
}
