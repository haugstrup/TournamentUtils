<?php
use PHPUnit\Framework\TestCase;

class DoubleEliminationBracketTest extends TestCase {

  public function testCanCalculateNumberOfRounds() {
    $players = array();
    for ($i=1;$i<=128;$i++) {
        $players[] = 'Seed#'.$i;
    }
    $bracket = new haugstrup\TournamentUtils\DoubleEliminationBracket(4, $players, array());

    $this->assertEquals(5, $bracket->number_of_rounds());

    $bracket->update_bracket_size(8);
    $this->assertEquals(8, $bracket->number_of_rounds());

    $bracket->update_bracket_size(16);
    $this->assertEquals(11, $bracket->number_of_rounds());

    $bracket->update_bracket_size(32);
    $this->assertEquals(14, $bracket->number_of_rounds());

    $bracket->update_bracket_size(64);
    $this->assertEquals(17, $bracket->number_of_rounds());

    $bracket->update_bracket_size(128);
    $this->assertEquals(20, $bracket->number_of_rounds());
  }

  public function testCanCalculateFinalsRoundIndex() {
    $players = array();
    for ($i=1;$i<=128;$i++) {
        $players[] = 'Seed#'.$i;
    }
    $bracket = new haugstrup\TournamentUtils\DoubleEliminationBracket(4, $players, array());

    $this->assertEquals(4, $bracket->finals_round_index());

    $bracket->update_bracket_size(8);
    $this->assertEquals(7, $bracket->finals_round_index());

    $bracket->update_bracket_size(16);
    $this->assertEquals(10, $bracket->finals_round_index());

    $bracket->update_bracket_size(32);
    $this->assertEquals(13, $bracket->finals_round_index());

    $bracket->update_bracket_size(64);
    $this->assertEquals(16, $bracket->finals_round_index());

    $bracket->update_bracket_size(128);
    $this->assertEquals(19, $bracket->finals_round_index());
  }

  public function testCanReturnWinnersRoundIndices() {
    $players = array();
    for ($i=1;$i<=128;$i++) {
        $players[] = 'Seed#'.$i;
    }
    $bracket = new haugstrup\TournamentUtils\DoubleEliminationBracket(4, $players, array());

    $this->assertEquals(array(0, 1), $bracket->winners_round_indices());

    $bracket->update_bracket_size(8);
    $this->assertEquals(array(0, 1, 2), $bracket->winners_round_indices());

    $bracket->update_bracket_size(16);
    $this->assertEquals(array(0, 1, 2, 3), $bracket->winners_round_indices());

    $bracket->update_bracket_size(32);
    $this->assertEquals(array(0, 1, 2, 3, 4), $bracket->winners_round_indices());

    $bracket->update_bracket_size(64);
    $this->assertEquals(array(0, 1, 2, 3, 4, 5), $bracket->winners_round_indices());

    $bracket->update_bracket_size(128);
    $this->assertEquals(array(0, 1, 2, 3, 4, 5, 6), $bracket->winners_round_indices());
  }

  public function testCanReturnLosersRoundIndices() {
    $players = array();
    for ($i=1;$i<=128;$i++) {
        $players[] = 'Seed#'.$i;
    }
    $bracket = new haugstrup\TournamentUtils\DoubleEliminationBracket(4, $players, array());

    $this->assertEquals(array(2, 3), $bracket->losers_round_indices());

    $bracket->update_bracket_size(8);
    $this->assertEquals(array(3, 4, 5, 6), $bracket->losers_round_indices());

    $bracket->update_bracket_size(16);
    $this->assertEquals(array(4, 5, 6, 7, 8, 9), $bracket->losers_round_indices());

    $bracket->update_bracket_size(32);
    $this->assertEquals(array(5, 6, 7, 8, 9, 10, 11, 12), $bracket->losers_round_indices());

    $bracket->update_bracket_size(64);
    $this->assertEquals(array(6, 7, 8, 9, 10, 11, 12, 13, 14, 15), $bracket->losers_round_indices());

    $bracket->update_bracket_size(128);
    $this->assertEquals(array(7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18), $bracket->losers_round_indices());
  }
}
