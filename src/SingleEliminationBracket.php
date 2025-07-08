<?php

namespace haugstrup\TournamentUtils;

require_once 'Base.php';

class SingleEliminationBracket extends Base
{
    public $players = [];

    public $bracket_size;

    public function __construct($bracket_size)
    {
        $this->bracket_size = $bracket_size;
    }

    /**
     * Get index for the two child games for a given game index.
     */
    public function children($parent_index)
    {
        return [$parent_index << 1, ($parent_index << 1) + 1];
    }

    /**
     * Get the index for the parent index for a given game index.
     */
    public function parent($child_index)
    {
        return $child_index >> 1;
    }

    /**
     * Get total number of games in the bracket.
     */
    public function game_count()
    {
        return $this->bracket_size - 1;
    }

    /**
     * Get the round index for a given game index.
     */
    public function round($index)
    {
        $n = -1;
        while ($index > 0) {
            $index >>= 1;
            $n++;
        }

        return $n;
    }

    /**
     * Get the list of game indexes for a given round index.
     */
    public function indexes_in_round($round)
    {
        $list = [];

        for ($i = 1; $i <= $this->game_count(); $i++) {
            if ($this->round($i) === $round) {
                $list[] = $i;
            }
        }

        return $list;
    }

    /**
     * Get the total number of rounds in bracket.
     */
    public function number_of_rounds()
    {
        return $this->round($this->game_count()) + 1;
    }

    /**
     * Return player seed numbers for each game in the first round.
     * Numeric keys refer to the heap index for that game
     */
    public function first_round_groups()
    {
        $map = [
            2 => [1 => [1, 2]],
            4 => [2 => [1, 4], 3 => [2, 3]],
            8 => [4 => [1, 8], 5 => [4, 5], 6 => [3, 6], 7 => [2, 7]],
            16 => [
                8 => [1, 16],
                9 => [8, 9],
                10 => [4, 13],
                11 => [5, 12],
                12 => [2, 15],
                13 => [7, 10],
                14 => [3, 14],
                15 => [6, 11],
            ],
            32 => [
                16 => [1, 32],
                17 => [16, 17],
                18 => [9, 24],
                19 => [8, 25],
                20 => [4, 29],
                21 => [13, 20],
                22 => [12, 21],
                23 => [5, 28],
                24 => [2, 31],
                25 => [15, 18],
                26 => [10, 23],
                27 => [7, 26],
                28 => [3, 30],
                29 => [14, 19],
                30 => [11, 22],
                31 => [6, 27],
            ],
            64 => [
                32 => [1, 64],
                33 => [32, 33],
                34 => [17, 48],
                35 => [16, 49],
                36 => [9, 56],
                37 => [24, 41],
                38 => [25, 40],
                39 => [8, 57],
                40 => [4, 61],
                41 => [29, 36],
                42 => [20, 45],
                43 => [13, 52],
                44 => [12, 53],
                45 => [21, 44],
                46 => [28, 37],
                47 => [5, 60],
                48 => [2, 63],
                49 => [31, 34],
                50 => [18, 47],
                51 => [15, 50],
                52 => [10, 55],
                53 => [23, 42],
                54 => [26, 39],
                55 => [7, 58],
                56 => [3, 62],
                57 => [30, 35],
                58 => [19, 46],
                59 => [14, 51],
                60 => [11, 54],
                61 => [22, 43],
                62 => [27, 38],
                63 => [6, 59],
            ],
            128 => [
                64 => [1, 128],
                65 => [64, 65],
                66 => [32, 97],
                67 => [33, 96],
                68 => [16, 113],
                69 => [49, 80],
                70 => [17, 112],
                71 => [48, 81],
                72 => [8, 121],
                73 => [57, 72],
                74 => [25, 104],
                75 => [40, 89],
                76 => [9, 120],
                77 => [56, 73],
                78 => [24, 105],
                79 => [41, 88],
                80 => [4, 125],
                81 => [61, 68],
                82 => [29, 100],
                83 => [36, 93],
                84 => [13, 116],
                85 => [52, 77],
                86 => [20, 109],
                87 => [45, 84],
                88 => [5, 124],
                89 => [60, 69],
                90 => [28, 101],
                91 => [37, 92],
                92 => [12, 117],
                93 => [53, 76],
                94 => [21, 108],
                95 => [44, 85],

                96 => [2, 127],
                97 => [63, 66],
                98 => [31, 98],
                99 => [34, 95],
                100 => [15, 114],
                101 => [50, 79],
                102 => [18, 111],
                103 => [47, 82],
                104 => [7, 122],
                105 => [58, 71],
                106 => [26, 103],
                107 => [39, 90],
                108 => [10, 119],
                109 => [55, 74],
                110 => [23, 106],
                111 => [42, 87],
                112 => [3, 126],
                113 => [62, 67],
                114 => [30, 99],
                115 => [35, 94],
                116 => [14, 115],
                117 => [51, 78],
                118 => [19, 110],
                119 => [46, 83],
                120 => [6, 123],
                121 => [59, 70],
                122 => [27, 102],
                123 => [38, 91],
                124 => [11, 118],
                125 => [54, 75],
                126 => [22, 107],
                127 => [43, 86],
            ],
        ];

        return $map[$this->bracket_size];
    }
}
