<?php

namespace haugstrup\TournamentUtils;

class Base
{
    // Allows array_rand and shuffle to be stubbed out in testing.
    public function array_rand($array, $number = 1)
    {
        return array_rand($array, $number);
    }

    public function shuffle($array)
    {
        shuffle($array);

        return $array;
    }
}
