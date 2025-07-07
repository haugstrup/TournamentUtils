<?php

use PHPUnit\Framework\TestCase;

class GlickoCalculatorTest extends TestCase
{
    public function test_calculates_g()
    {
        $this->assertEquals(0.9955, round(haugstrup\TournamentUtils\GlickoCalculator::g(30), 4));
    }

    public function test_calculates_e()
    {
        $this->assertEquals(0.6395, round(haugstrup\TournamentUtils\GlickoCalculator::E(1500, 1400, 30), 4));
    }

    public function test_calculates_expected_outcome()
    {
        $this->assertEquals(0.376, round(haugstrup\TournamentUtils\GlickoCalculator::expectedOutcome(1400, 80, 1500, 150), 3));
    }

    public function test_advances_rd()
    {
        $calculator = new haugstrup\TournamentUtils\GlickoCalculator;
        $this->assertEquals(33.2207, round($calculator->advanceRD(30), 4));
        $this->assertEquals(350, round($calculator->advanceRD(350), 4));
        $this->assertEquals(36.1557, round($calculator->advanceRD(30, 2), 4));
        $this->assertEquals(43.7959, round($calculator->advanceRD(30, 5), 4));
        $this->assertEquals(350, round($calculator->advanceRD(250, 300), 4));
    }

    public function test_adds_head_to_head_result()
    {
        $calculator = new haugstrup\TournamentUtils\GlickoCalculator;
        $calculator->addResult([1, 2]);

        $result = [
            1 => [['outcome' => 1, 'opponent' => 2, 'adjustment' => null]],
            2 => [['outcome' => 0, 'opponent' => 1, 'adjustment' => null]],
        ];

        $this->assertEquals($result, $calculator->getResults());
    }

    public function test_adds_result_with_group_size()
    {
        $calculator = new haugstrup\TournamentUtils\GlickoCalculator;
        $calculator->addResult([1, 2], 4, 4);

        $result = [
            1 => [['outcome' => 1, 'opponent' => 2, 'adjustment' => 1.7320508075688772]],
            2 => [['outcome' => 0, 'opponent' => 1, 'adjustment' => 1.7320508075688772]],
        ];

        $this->assertEquals($result, $calculator->getResults());
    }

    public function test_adds_three_player_result()
    {
        $calculator = new haugstrup\TournamentUtils\GlickoCalculator;
        $calculator->addResult([1, 2, 3]);

        $result = [
            1 => [
                ['outcome' => 1, 'opponent' => 2, 'adjustment' => 1.4142135623730951],
                ['outcome' => 1, 'opponent' => 3, 'adjustment' => 1.4142135623730951],
            ],
            2 => [
                ['outcome' => 0, 'opponent' => 1, 'adjustment' => 1.4142135623730951],
                ['outcome' => 1, 'opponent' => 3, 'adjustment' => 1.4142135623730951],
            ],
            3 => [
                ['outcome' => 0, 'opponent' => 1, 'adjustment' => 1.4142135623730951],
                ['outcome' => 0, 'opponent' => 2, 'adjustment' => 1.4142135623730951],
            ],
        ];

        $this->assertEquals($result, $calculator->getResults());
    }

    public function test_adds_four_player_result()
    {
        $calculator = new haugstrup\TournamentUtils\GlickoCalculator;
        $calculator->addResult([1, 2, 3, 4]);

        $result = [
            1 => [
                ['outcome' => 1, 'opponent' => 2, 'adjustment' => 1.7320508075688772],
                ['outcome' => 1, 'opponent' => 3, 'adjustment' => 1.7320508075688772],
                ['outcome' => 1, 'opponent' => 4, 'adjustment' => 1.7320508075688772],
            ],
            2 => [
                ['outcome' => 0, 'opponent' => 1, 'adjustment' => 1.7320508075688772],
                ['outcome' => 1, 'opponent' => 3, 'adjustment' => 1.7320508075688772],
                ['outcome' => 1, 'opponent' => 4, 'adjustment' => 1.7320508075688772],
            ],
            3 => [
                ['outcome' => 0, 'opponent' => 1, 'adjustment' => 1.7320508075688772],
                ['outcome' => 0, 'opponent' => 2, 'adjustment' => 1.7320508075688772],
                ['outcome' => 1, 'opponent' => 4, 'adjustment' => 1.7320508075688772],
            ],
            4 => [
                ['outcome' => 0, 'opponent' => 1, 'adjustment' => 1.7320508075688772],
                ['outcome' => 0, 'opponent' => 2, 'adjustment' => 1.7320508075688772],
                ['outcome' => 0, 'opponent' => 3, 'adjustment' => 1.7320508075688772],
            ],
        ];

        $this->assertEquals($result, $calculator->getResults());
    }

    public function test_adds_draw_result()
    {
        $calculator = new haugstrup\TournamentUtils\GlickoCalculator;
        $calculator->addDraw([1, 2]);

        $result = [
            1 => [['outcome' => 0.5, 'opponent' => 2, 'adjustment' => null]],
            2 => [['outcome' => 0.5, 'opponent' => 1, 'adjustment' => null]],
        ];

        $this->assertEquals($result, $calculator->getResults());
    }

    public function test_adds_draw_result_with_group_size()
    {
        $calculator = new haugstrup\TournamentUtils\GlickoCalculator;
        $calculator->addDraw([1, 2], 3);

        $result = [
            1 => [['outcome' => 0.5, 'opponent' => 2, 'adjustment' => 1.4142135623730951]],
            2 => [['outcome' => 0.5, 'opponent' => 1, 'adjustment' => 1.4142135623730951]],
        ];

        $this->assertEquals($result, $calculator->getResults());
    }

    public function test_can_calculate_new_rd()
    {
        $calculator = new haugstrup\TournamentUtils\GlickoCalculator;
        $calculator->addPlayers([
            1 => ['rating' => 1500, 'rd' => 200],
            2 => ['rating' => 1400, 'rd' => 30],
            3 => ['rating' => 1550, 'rd' => 100],
            4 => ['rating' => 1700, 'rd' => 300],
        ]);
        $calculator->addResult([1, 2]);
        $calculator->addResult([3, 1]);
        $calculator->addResult([4, 1]);

        $this->assertEquals(151.4002, round($calculator->calculateNewRDForPlayer(1), 4));
    }

    public function test_can_calculate_new_rating()
    {
        $calculator = new haugstrup\TournamentUtils\GlickoCalculator;
        $calculator->addPlayers([
            1 => ['rating' => 1500, 'rd' => 200],
            2 => ['rating' => 1400, 'rd' => 30],
            3 => ['rating' => 1550, 'rd' => 100],
            4 => ['rating' => 1700, 'rd' => 300],
        ]);
        $calculator->addResult([1, 2]);
        $calculator->addResult([3, 1]);
        $calculator->addResult([4, 1]);

        $new_rd = $calculator->calculateNewRDForPlayer(1);
        $new_rating = $calculator->calculateNewRatingForPlayer(1, $new_rd);

        $this->assertEquals(1464.1108, round($new_rating, 4));
    }

    public function test_can_update_ratings()
    {
        $calculator = new haugstrup\TournamentUtils\GlickoCalculator;
        $calculator->addPlayers([
            1 => ['rating' => 1500, 'rd' => 200],
            2 => ['rating' => 1400, 'rd' => 30],
            3 => ['rating' => 1550, 'rd' => 100],
            4 => ['rating' => 1700, 'rd' => 300],
        ]);
        $calculator->addResult([1, 2]);
        $calculator->addResult([3, 1]);
        $calculator->addResult([4, 1]);

        $outcome = [
            1 => [
                'rating' => 1500,
                'rd' => 200.51,
                'new_rd' => 151.67,
                'new_rating' => 1464.00,
            ],
            2 => [
                'rating' => 1400,
                'rd' => 33.22,
                'new_rd' => 33.12,
                'new_rating' => 1397.97,
            ],
            3 => [
                'rating' => 1550,
                'rd' => 101.01,
                'new_rd' => 98.15,
                'new_rating' => 1570.56,
            ],
            4 => [
                'rating' => 1700,
                'rd' => 300.34,
                'new_rd' => 251.70,
                'new_rating' => 1784.49,
            ],
        ];

        $result = $calculator->updateRatings();

        $this->assertEquals($result[1]['rating'], $outcome[1]['rating']);
        $this->assertEquals(round($result[1]['rd'], 2), $outcome[1]['rd']);
        $this->assertEquals(round($result[1]['new_rd'], 2), $outcome[1]['new_rd']);
        $this->assertEquals(round($result[1]['new_rating'], 2), $outcome[1]['new_rating']);

        $this->assertEquals($result[2]['rating'], $outcome[2]['rating']);
        $this->assertEquals(round($result[2]['rd'], 2), $outcome[2]['rd']);
        $this->assertEquals(round($result[2]['new_rd'], 2), $outcome[2]['new_rd']);
        $this->assertEquals(round($result[2]['new_rating'], 2), $outcome[2]['new_rating']);

        $this->assertEquals($result[3]['rating'], $outcome[3]['rating']);
        $this->assertEquals(round($result[3]['rd'], 2), $outcome[3]['rd']);
        $this->assertEquals(round($result[3]['new_rd'], 2), $outcome[3]['new_rd']);
        $this->assertEquals(round($result[3]['new_rating'], 2), $outcome[3]['new_rating']);

        $this->assertEquals($result[4]['rating'], $outcome[4]['rating']);
        $this->assertEquals(round($result[4]['rd'], 2), $outcome[4]['rd']);
        $this->assertEquals(round($result[4]['new_rd'], 2), $outcome[4]['new_rd']);
        $this->assertEquals(round($result[4]['new_rating'], 2), $outcome[4]['new_rating']);
    }
}
