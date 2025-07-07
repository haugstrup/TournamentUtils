<?php

namespace haugstrup\TournamentUtils;

require_once 'Base.php';

class CrossPairing extends Base
{
    public $players = [];

    public function __construct($players)
    {
        $this->players = $players;
    }

    public function build()
    {
        $players = $this->players;
        $groups = ['groups' => [], 'byes' => []];

        if (count($this->players) % 2 != 0) {
            $bye_slice = array_splice($players, -1, 1);
            $groups['byes'][] = $bye_slice[0];
        }

        $split = array_chunk($players, count($players) / 2);

        foreach ($split[0] as $index => $player) {
            $groups['groups'][] = [$player, $split[1][$index]];
        }

        return $groups;
    }
}
