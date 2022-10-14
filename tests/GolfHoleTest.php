<?php
use PHPUnit\Framework\TestCase;

class GolfHoleTest extends TestCase
{
    public function testBuilds10MillionBrackets()
    {
        $hole = new haugstrup\TournamentUtils\GolfHole(10000000);
        $expected = [
			6 => [8000000, 9999999],
			7 => [6000000, 7999999],
			8 => [4000000, 5999999],
			9 => [2000000, 3999999],
			10 => [0, 1999999],
		];
		$result = $hole->getScoreBrackets();

        $this->assertEquals($result, $expected);
    }

	public function testBuilds500MillionBrackets()
    {
        $hole = new haugstrup\TournamentUtils\GolfHole(500000000);
        $expected = [
			6 => [400000000, 499999999],
			7 => [300000000, 399999999],
			8 => [200000000, 299999999],
			9 => [100000000, 199999999],
			10 => [0, 99999999],
		];
		$result = $hole->getScoreBrackets();

        $this->assertEquals($result, $expected);
    }

	public function testBuildsCalculatesStrokes()
    {
        $hole = new haugstrup\TournamentUtils\GolfHole(10000000);

		$this->assertEquals($hole->getStrokesForScore(null), 10);
		$this->assertEquals($hole->getStrokesForScore(0), 10);
        $this->assertEquals($hole->getStrokesForScore(1), 10);
        $this->assertEquals($hole->getStrokesForScore(1000000), 10);
        $this->assertEquals($hole->getStrokesForScore(1999999), 10);

		$this->assertEquals($hole->getStrokesForScore(2000000), 9);
        $this->assertEquals($hole->getStrokesForScore(3000000), 9);
        $this->assertEquals($hole->getStrokesForScore(3999999), 9);

		$this->assertEquals($hole->getStrokesForScore(4000000), 8);
        $this->assertEquals($hole->getStrokesForScore(5000000), 8);
        $this->assertEquals($hole->getStrokesForScore(5999999), 8);

		$this->assertEquals($hole->getStrokesForScore(6000000), 7);
        $this->assertEquals($hole->getStrokesForScore(7000000), 7);
        $this->assertEquals($hole->getStrokesForScore(7999999), 7);

		$this->assertEquals($hole->getStrokesForScore(8000000), 6);
        $this->assertEquals($hole->getStrokesForScore(9000000), 6);
        $this->assertEquals($hole->getStrokesForScore(9999999), 6);

		$this->assertEquals($hole->getStrokesForScore(10000000), 0);
		$this->assertEquals($hole->getStrokesForScore(20000000), 0);
    }

}