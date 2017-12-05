<?php
use PHPUnit\Framework\TestCase;

class DoubleEliminationLoserBracketTest extends TestCase {

  public function testCanCalculateNumberOfRounds() {
    $bracket = new haugstrup\TournamentUtils\DoubleEliminationLoserBracket(4);

    $this->assertEquals(2, $bracket->number_of_rounds());

    $bracket->bracket_size = 8;
    $this->assertEquals(4, $bracket->number_of_rounds());

    $bracket->bracket_size = 16;
    $this->assertEquals(6, $bracket->number_of_rounds());

    $bracket->bracket_size = 32;
    $this->assertEquals(8, $bracket->number_of_rounds());

    $bracket->bracket_size = 64;
    $this->assertEquals(10, $bracket->number_of_rounds());

    $bracket->bracket_size = 128;
    $this->assertEquals(12, $bracket->number_of_rounds());
  }

  public function testCanCalculateGamesPerRound() {
    $bracket = new haugstrup\TournamentUtils\DoubleEliminationLoserBracket(4);

    $this->assertEquals(1, $bracket->game_count_for_round(0));
    $this->assertEquals(1, $bracket->game_count_for_round(1));

    $bracket->bracket_size = 8;
    $this->assertEquals(2, $bracket->game_count_for_round(0));
    $this->assertEquals(2, $bracket->game_count_for_round(1));
    $this->assertEquals(1, $bracket->game_count_for_round(2));
    $this->assertEquals(1, $bracket->game_count_for_round(3));

    $bracket->bracket_size = 16;
    $this->assertEquals(4, $bracket->game_count_for_round(0));
    $this->assertEquals(4, $bracket->game_count_for_round(1));
    $this->assertEquals(2, $bracket->game_count_for_round(2));
    $this->assertEquals(2, $bracket->game_count_for_round(3));
    $this->assertEquals(1, $bracket->game_count_for_round(4));
    $this->assertEquals(1, $bracket->game_count_for_round(5));

    $bracket->bracket_size = 32;
    $this->assertEquals(8, $bracket->game_count_for_round(0));
    $this->assertEquals(8, $bracket->game_count_for_round(1));
    $this->assertEquals(4, $bracket->game_count_for_round(2));
    $this->assertEquals(4, $bracket->game_count_for_round(3));
    $this->assertEquals(2, $bracket->game_count_for_round(4));
    $this->assertEquals(2, $bracket->game_count_for_round(5));
    $this->assertEquals(1, $bracket->game_count_for_round(6));
    $this->assertEquals(1, $bracket->game_count_for_round(7));

    $bracket->bracket_size = 64;
    $this->assertEquals(16, $bracket->game_count_for_round(0));
    $this->assertEquals(16, $bracket->game_count_for_round(1));
    $this->assertEquals(8, $bracket->game_count_for_round(2));
    $this->assertEquals(8, $bracket->game_count_for_round(3));
    $this->assertEquals(4, $bracket->game_count_for_round(4));
    $this->assertEquals(4, $bracket->game_count_for_round(5));
    $this->assertEquals(2, $bracket->game_count_for_round(6));
    $this->assertEquals(2, $bracket->game_count_for_round(7));

    $this->assertEquals(1, $bracket->game_count_for_round(8));

    $this->assertEquals(1, $bracket->game_count_for_round(9));

    $bracket->bracket_size = 128;
    $this->assertEquals(32, $bracket->game_count_for_round(0));
    $this->assertEquals(32, $bracket->game_count_for_round(1));
    $this->assertEquals(16, $bracket->game_count_for_round(2));
    $this->assertEquals(16, $bracket->game_count_for_round(3));
    $this->assertEquals(8, $bracket->game_count_for_round(4));
    $this->assertEquals(8, $bracket->game_count_for_round(5));
    $this->assertEquals(4, $bracket->game_count_for_round(6));
    $this->assertEquals(4, $bracket->game_count_for_round(7));
    $this->assertEquals(2, $bracket->game_count_for_round(8));
    $this->assertEquals(2, $bracket->game_count_for_round(9));
    $this->assertEquals(1, $bracket->game_count_for_round(10));
    $this->assertEquals(1, $bracket->game_count_for_round(11));
  }

  public function testCanTellIfRoundBringsInWinners() {
    $bracket = new haugstrup\TournamentUtils\DoubleEliminationLoserBracket(4);

    $this->assertTrue($bracket->round_brings_in_winners(0));
    $this->assertTrue($bracket->round_brings_in_winners(1));

    $bracket->bracket_size = 8;
    $this->assertTrue($bracket->round_brings_in_winners(0));
    $this->assertTrue($bracket->round_brings_in_winners(1));
    $this->assertFalse($bracket->round_brings_in_winners(2));
    $this->assertTrue($bracket->round_brings_in_winners(3));

    $bracket->bracket_size = 16;
    $this->assertTrue($bracket->round_brings_in_winners(0));
    $this->assertTrue($bracket->round_brings_in_winners(1));
    $this->assertFalse($bracket->round_brings_in_winners(2));
    $this->assertTrue($bracket->round_brings_in_winners(3));
    $this->assertFalse($bracket->round_brings_in_winners(4));
    $this->assertTrue($bracket->round_brings_in_winners(5));

    $bracket->bracket_size = 32;
    $this->assertTrue($bracket->round_brings_in_winners(0));
    $this->assertTrue($bracket->round_brings_in_winners(1));
    $this->assertFalse($bracket->round_brings_in_winners(2));
    $this->assertTrue($bracket->round_brings_in_winners(3));
    $this->assertFalse($bracket->round_brings_in_winners(4));
    $this->assertTrue($bracket->round_brings_in_winners(5));
    $this->assertFalse($bracket->round_brings_in_winners(6));
    $this->assertTrue($bracket->round_brings_in_winners(7));

    $bracket->bracket_size = 64;
    $this->assertTrue($bracket->round_brings_in_winners(0));
    $this->assertTrue($bracket->round_brings_in_winners(1));
    $this->assertFalse($bracket->round_brings_in_winners(2));
    $this->assertTrue($bracket->round_brings_in_winners(3));
    $this->assertFalse($bracket->round_brings_in_winners(4));
    $this->assertTrue($bracket->round_brings_in_winners(5));
    $this->assertFalse($bracket->round_brings_in_winners(6));
    $this->assertTrue($bracket->round_brings_in_winners(7));
    $this->assertFalse($bracket->round_brings_in_winners(8));
    $this->assertTrue($bracket->round_brings_in_winners(9));

    $bracket->bracket_size = 128;
    $this->assertTrue($bracket->round_brings_in_winners(0));
    $this->assertTrue($bracket->round_brings_in_winners(1));
    $this->assertFalse($bracket->round_brings_in_winners(2));
    $this->assertTrue($bracket->round_brings_in_winners(3));
    $this->assertFalse($bracket->round_brings_in_winners(4));
    $this->assertTrue($bracket->round_brings_in_winners(5));
    $this->assertFalse($bracket->round_brings_in_winners(6));
    $this->assertTrue($bracket->round_brings_in_winners(7));
    $this->assertFalse($bracket->round_brings_in_winners(8));
    $this->assertTrue($bracket->round_brings_in_winners(9));
    $this->assertFalse($bracket->round_brings_in_winners(10));
    $this->assertTrue($bracket->round_brings_in_winners(11));
  }
}
