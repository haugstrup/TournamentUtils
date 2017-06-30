<?php
use PHPUnit\Framework\TestCase;

class GlickoCalculatorTest extends TestCase {

  public function testCalculatesG() {
    $this->assertEquals(0.9955, round(haugstrup\TournamentUtils\GlickoCalculator::g(30), 4));
  }

  public function testCalculatesE() {
    $this->assertEquals(0.6395, round(haugstrup\TournamentUtils\GlickoCalculator::E(1500, 1400, 30), 4));
  }

  public function testAdvancesRD() {
    $calculator = new haugstrup\TournamentUtils\GlickoCalculator();
    $this->assertEquals(33.2207, round($calculator->advanceRD(30), 4));
    $this->assertEquals(350, round($calculator->advanceRD(350), 4));
    $this->assertEquals(36.1557, round($calculator->advanceRD(30, 2), 4));
    $this->assertEquals(43.7959, round($calculator->advanceRD(30, 5), 4));
    $this->assertEquals(350, round($calculator->advanceRD(250, 300), 4));
  }

  public function testAddsHeadToHeadResult() {
    $calculator = new haugstrup\TournamentUtils\GlickoCalculator();
    $calculator->addResult([1, 2]);

    $result = [
      1 => [['outcome' => 1, 'opponent' => 2, 'adjustment' => null]],
      2 => [['outcome' => 0, 'opponent' => 1, 'adjustment' => null]],
    ];

    $this->assertEquals($result, $calculator->getResults());
  }

  public function testAddsResultWithGroupSize() {
    $calculator = new haugstrup\TournamentUtils\GlickoCalculator();
    $calculator->addResult([1, 2], 4, 4);

    $result = [
      1 => [['outcome' => 1, 'opponent' => 2, 'adjustment' => 1.7320508075688772]],
      2 => [['outcome' => 0, 'opponent' => 1, 'adjustment' => 1.7320508075688772]],
    ];

    $this->assertEquals($result, $calculator->getResults());
  }

  public function testAddsThreePlayerResult() {
    $calculator = new haugstrup\TournamentUtils\GlickoCalculator();
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

  public function testAddsFourPlayerResult() {
    $calculator = new haugstrup\TournamentUtils\GlickoCalculator();
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

  public function testAddsDrawResult() {
    $calculator = new haugstrup\TournamentUtils\GlickoCalculator();
    $calculator->addDraw([1, 2]);

    $result = [
      1 => [['outcome' => 0.5, 'opponent' => 2, 'adjustment' => null]],
      2 => [['outcome' => 0.5, 'opponent' => 1, 'adjustment' => null]],
    ];

    $this->assertEquals($result, $calculator->getResults());
  }

  public function testAddsDrawResultWithGroupSize() {
    $calculator = new haugstrup\TournamentUtils\GlickoCalculator();
    $calculator->addDraw([1, 2], 3);

    $result = [
      1 => [['outcome' => 0.5, 'opponent' => 2, 'adjustment' => 1.4142135623730951]],
      2 => [['outcome' => 0.5, 'opponent' => 1, 'adjustment' => 1.4142135623730951]],
    ];

    $this->assertEquals($result, $calculator->getResults());
  }

  public function testCanCalculateNewRD() {
    $calculator = new haugstrup\TournamentUtils\GlickoCalculator();
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

  public function testCanCalculateNewRating() {
    $calculator = new haugstrup\TournamentUtils\GlickoCalculator();
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

  public function testCanUpdateRatings() {
    $calculator = new haugstrup\TournamentUtils\GlickoCalculator();
    $calculator->addPlayers([
      1 => ['rating' => 1500, 'rd' => 200],
      2 => ['rating' => 1400, 'rd' => 30],
      3 => ['rating' => 1550, 'rd' => 100],
      4 => ['rating' => 1700, 'rd' => 300],
    ]);
    $calculator->addResult([1, 2]);
    $calculator->addResult([3, 1]);
    $calculator->addResult([4, 1]);

    $result = [
        1 => [
            'rating' => 1500,
            'rd' => 200.50839328158,
            'new_rd' => 151.67289288472,
            'new_rating' => 1464.0036609438,
        ],
        2 => [
            'rating' => 1400,
            'rd' => 33.220713062185,
            'new_rd' => 33.119222327668,
            'new_rating' => 1397.9709302107,
        ],
        3 => [
            'rating' => 1550,
            'rd' => 101.0129485579,
            'new_rd' => 98.145450528184,
            'new_rating' => 1570.5647663985,
        ],
        4 => [
            'rating' => 1700,
            'rd' => 300.33916790249,
            'new_rd' => 251.7031108919,
            'new_rating' => 1784.4896381663,
        ],
    ];

    $this->assertEquals($result, $calculator->updateRatings());
  }

}