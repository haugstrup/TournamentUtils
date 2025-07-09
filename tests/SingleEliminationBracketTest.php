<?php

use PHPUnit\Framework\TestCase;

class SingleEliminationBracketTest extends TestCase
{
    public function test_game_count()
    {
        $expected = [
            [4, 3],
            [8, 7],
            [16, 15],
            [32, 31],
            [64, 63],
            [128, 127],
        ];

        foreach ($expected as $item) {
            foreach ([false, true] as $double_byes) {
                $bracket = new haugstrup\TournamentUtils\SingleEliminationBracket($item[0], $double_byes);
                $this->assertEquals($bracket->game_count(), $item[1]);
            }
        }
    }

    public function test_number_of_rounds()
    {
        $expected = [
            [4, 2],
            [8, 3],
            [16, 4],
            [32, 5],
            [64, 6],
            [128, 7],
        ];

        foreach ($expected as $item) {
            $bracket = new haugstrup\TournamentUtils\SingleEliminationBracket($item[0]);
            $this->assertEquals($bracket->number_of_rounds(), $item[1]);
        }
    }

    public function test_number_of_rounds_double_bye()
    {
        $bracket = new haugstrup\TournamentUtils\SingleEliminationBracket(32, true);
        $this->assertEquals($bracket->number_of_rounds(), 6);
    }

    public function test_parent()
    {
        $expected = [
            [0, 0],
            [1, 0],
            [2, 1],
            [3, 1],
            [4, 2],
            [5, 2],
            [6, 3],
            [7, 3],
            [8, 4],
            [9, 4],
            [10, 5],
            [11, 5],
            [12, 6],
            [13, 6],
            [14, 7],
            [15, 7],
            [16, 8],
            [17, 8],
            [18, 9],
            [19, 9],
            [20, 10],
            [21, 10],
            [22, 11],
            [23, 11],
            [24, 12],
            [25, 12],
            [26, 13],
            [27, 13],
            [28, 14],
            [29, 14],
            [30, 15],
            [31, 15],
            [32, 16],
            [33, 16],
            [34, 17],
            [35, 17],
            [36, 18],
            [37, 18],
            [38, 19],
            [39, 19],
            [40, 20],
            [41, 20],
            [42, 21],
            [43, 21],
            [44, 22],
            [45, 22],
            [46, 23],
            [47, 23],
            [48, 24],
            [49, 24],
            [50, 25],
            [51, 25],
            [52, 26],
            [53, 26],
            [54, 27],
            [55, 27],
            [56, 28],
            [57, 28],
            [58, 29],
            [59, 29],
            [60, 30],
            [61, 30],
            [62, 31],
            [63, 31],
        ];

        $bracket = new haugstrup\TournamentUtils\SingleEliminationBracket(64);
        foreach ($expected as $item) {
            $this->assertEquals($bracket->parent($item[0]), $item[1]);
        }
    }

    public function test_parent_double_bye()
    {
        $bracket = new haugstrup\TournamentUtils\SingleEliminationBracket(32, true);
        $this->assertEquals($bracket->parent(24), 16);
        $this->assertEquals($bracket->parent(8), 4);
        $this->assertEquals($bracket->parent(1), 0);
    }

    public function test_children()
    {
        $expected = [
            [0, [0, 1]],
            [1, [2, 3]],
            [2, [4, 5]],
            [3, [6, 7]],
            [4, [8, 9]],
            [5, [10, 11]],
            [6, [12, 13]],
            [7, [14, 15]],
            [8, [16, 17]],
            [9, [18, 19]],
            [10, [20, 21]],
            [11, [22, 23]],
            [12, [24, 25]],
            [13, [26, 27]],
            [14, [28, 29]],
            [15, [30, 31]],
            [16, [32, 33]],
            [17, [34, 35]],
            [18, [36, 37]],
            [19, [38, 39]],
            [20, [40, 41]],
            [21, [42, 43]],
            [22, [44, 45]],
            [23, [46, 47]],
            [24, [48, 49]],
            [25, [50, 51]],
            [26, [52, 53]],
            [27, [54, 55]],
            [28, [56, 57]],
            [29, [58, 59]],
            [30, [60, 61]],
            [31, [62, 63]],
        ];

        $bracket = new haugstrup\TournamentUtils\SingleEliminationBracket(64);
        foreach ($expected as $item) {
            $children = $bracket->children($item[0]);
            $this->assertEquals($item[1][0], $children[0]);
            $this->assertEquals($item[1][1], $children[1]);
        }
    }

    public function test_children_double_bye()
    {
        $expected = [
            [0, [0, 1]],
            [1, [2, 3]],
            [2, [4, 5]],
            [3, [6, 7]],
            [4, [8, 9]],
            [5, [10, 11]],
            [6, [12, 13]],
            [7, [14, 15]],
            [8, [-1, 16]],
            [15, [-1, 33]],
            [24, [-1, -1]],
        ];

        $bracket = new haugstrup\TournamentUtils\SingleEliminationBracket(32, true);
        foreach ($expected as $item) {
            $children = $bracket->children($item[0]);
            $this->assertEquals($item[1][0], $children[0]);
            $this->assertEquals($item[1][1], $children[1]);
        }
    }

    public function test_round()
    {
        $expected = [
            [0, -1],
            [1, 0],
            [2, 1],
            [3, 1],
            [4, 2],
            [5, 2],
            [6, 2],
            [7, 2],
            [8, 3],
            [9, 3],
            [10, 3],
            [11, 3],
            [12, 3],
            [13, 3],
            [14, 3],
            [15, 3],
            [16, 4],
            [17, 4],
            [18, 4],
            [19, 4],
            [20, 4],
            [21, 4],
            [22, 4],
            [23, 4],
            [24, 4],
            [25, 4],
            [26, 4],
            [27, 4],
            [28, 4],
            [29, 4],
            [30, 4],
            [31, 4],
            [32, 5],
            [33, 5],
            [34, 5],
            [35, 5],
            [36, 5],
            [37, 5],
            [38, 5],
            [39, 5],
            [40, 5],
            [41, 5],
            [42, 5],
            [43, 5],
            [44, 5],
            [45, 5],
            [46, 5],
            [47, 5],
            [48, 5],
            [49, 5],
            [50, 5],
            [51, 5],
            [52, 5],
            [53, 5],
            [54, 5],
            [55, 5],
            [56, 5],
            [57, 5],
            [58, 5],
            [59, 5],
            [60, 5],
            [61, 5],
            [62, 5],
            [63, 5],
            [64, 6],
            [127, 6],
            [128, 7],
        ];

        $bracket = new haugstrup\TournamentUtils\SingleEliminationBracket(64);
        foreach ($expected as $item) {
            $this->assertEquals($item[1], $bracket->round($item[0]));
        }
    }

    public function test_round_double_bye()
    {
        $expected = [
            [0, -1],
            [1, 0],
            [2, 1],
            [3, 1],
            [4, 2],
            [5, 2],
            [6, 2],
            [7, 2],
            [8, 3],
            [9, 3],
            [10, 3],
            [11, 3],
            [12, 3],
            [13, 3],
            [14, 3],
            [15, 3],
            [16, 4],
            [17, 4],
            [18, 4],
            [19, 4],
            [20, 4],
            [21, 4],
            [22, 4],
            [23, 4],
            [24, 5],
            [25, 5],
            [26, 5],
            [27, 5],
            [28, 5],
            [29, 5],
            [30, 5],
            [31, 5],
        ];

        $bracket = new haugstrup\TournamentUtils\SingleEliminationBracket(32, true);
        foreach ($expected as $item) {
            $this->assertEquals($item[1], $bracket->round($item[0]));
        }
    }

    public function test_initial_groups()
    {
        $bracket = new haugstrup\TournamentUtils\SingleEliminationBracket(8);
        $this->assertEquals($bracket->initial_groups(), [
            [
                'game' => 4,
                'round' => 2,
                'p1' => 'S1',
                'p2' => 'S8',
            ],
            [
                'game' => 5,
                'round' => 2,
                'p1' => 'S4',
                'p2' => 'S5',
            ],
            [
                'game' => 6,
                'round' => 2,
                'p1' => 'S3',
                'p2' => 'S6',
            ],
            [
                'game' => 7,
                'round' => 2,
                'p1' => 'S2',
                'p2' => 'S7',
            ],
        ]);
    }

    public function test_initial_groups_double_bye()
    {
        $bracket = new haugstrup\TournamentUtils\SingleEliminationBracket(32, true);
        $this->assertEquals($bracket->initial_groups(), [
            ['game' => 24, 'round' => 5, 'p1' => 'S17', 'p2' => 'S32'],
            ['game' => 25, 'round' => 5, 'p1' => 'S24', 'p2' => 'S25'],
            ['game' => 26, 'round' => 5, 'p1' => 'S20', 'p2' => 'S29'],
            ['game' => 27, 'round' => 5, 'p1' => 'S21', 'p2' => 'S28'],
            ['game' => 28, 'round' => 5, 'p1' => 'S18', 'p2' => 'S31'],
            ['game' => 29, 'round' => 5, 'p1' => 'S23', 'p2' => 'S26'],
            ['game' => 30, 'round' => 5, 'p1' => 'S19', 'p2' => 'S30'],
            ['game' => 31, 'round' => 5, 'p1' => 'S22', 'p2' => 'S27'],

            ['game' => 16, 'round' => 4, 'p1' => 'S16', 'p2' => 'W24'],
            ['game' => 17, 'round' => 4, 'p1' => 'S9', 'p2' => 'W25'],
            ['game' => 18, 'round' => 4, 'p1' => 'S13', 'p2' => 'W26'],
            ['game' => 19, 'round' => 4, 'p1' => 'S12', 'p2' => 'W27'],
            ['game' => 20, 'round' => 4, 'p1' => 'S15', 'p2' => 'W28'],
            ['game' => 21, 'round' => 4, 'p1' => 'S10', 'p2' => 'W29'],
            ['game' => 22, 'round' => 4, 'p1' => 'S14', 'p2' => 'W30'],
            ['game' => 23, 'round' => 4, 'p1' => 'S11', 'p2' => 'W31'],

            ['game' => 8, 'round' => 3, 'p1' => 'S1', 'p2' => 'W16'],
            ['game' => 9, 'round' => 3, 'p1' => 'S8', 'p2' => 'W17'],
            ['game' => 10, 'round' => 3, 'p1' => 'S4', 'p2' => 'W18'],
            ['game' => 11, 'round' => 3, 'p1' => 'S5', 'p2' => 'W19'],
            ['game' => 12, 'round' => 3, 'p1' => 'S2', 'p2' => 'W20'],
            ['game' => 13, 'round' => 3, 'p1' => 'S7', 'p2' => 'W21'],
            ['game' => 14, 'round' => 3, 'p1' => 'S3', 'p2' => 'W22'],
            ['game' => 15, 'round' => 3, 'p1' => 'S6', 'p2' => 'W33'],
        ]);
    }
}
