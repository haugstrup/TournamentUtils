<?php

namespace haugstrup\TournamentUtils;

class GolfHole
{
    public $targetScore = 0;

    public function __construct($targetScore)
    {
        $this->targetScore = $targetScore;
    }

    public function getScoreBrackets()
    {
        return [
            6 => [(int) $this->targetScore * 0.8, $this->targetScore - 1],
            7 => [(int) $this->targetScore * 0.6, (int) $this->targetScore * 0.8 - 1],
            8 => [(int) $this->targetScore * 0.4, (int) $this->targetScore * 0.6 - 1],
            9 => [(int) $this->targetScore * 0.2, (int) $this->targetScore * 0.4 - 1],
            10 => [0, (int) $this->targetScore * 0.2 - 1],
        ];
    }

    public function getStrokesForScore($score)
    {
        if (! $score) {
            return 10;
        }
        if ($score >= $this->targetScore) {
            return 0;
        }

        $brackets = $this->getScoreBrackets();

        if ($score >= $brackets[6][0] && $score <= $brackets[6][1]) {
            return 6;
        }
        if ($score >= $brackets[7][0] && $score <= $brackets[7][1]) {
            return 7;
        }
        if ($score >= $brackets[8][0] && $score <= $brackets[8][1]) {
            return 8;
        }
        if ($score >= $brackets[9][0] && $score <= $brackets[9][1]) {
            return 9;
        }

        return 10;
    }
}
