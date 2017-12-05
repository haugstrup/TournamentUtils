<?php namespace haugstrup\TournamentUtils;

require_once 'SingleEliminationBracket.php';
require_once 'DoubleEliminationLoserBracket.php';

class DoubleEliminationBracket extends Base {

  public $bracket_size = 8;
  public $players = array();

  public function __construct($bracket_size, $players, $winners_by_heap_index) {
    $this->bracket_size = $bracket_size;
    $this->players = $players;

    $this->winners = new SingleEliminationBracket($this->bracket_size, $this->players, $winners_by_heap_index);
    $this->losers = new DoubleEliminationLoserBracket($this->bracket_size);
  }

  /**
   * Change the bracket size after the bracket has been created.
   * Used for unit tests.
   */
  public function update_bracket_size($bracket_size) {
    $this->bracket_size = $bracket_size;
    $this->winners->bracket_size = $bracket_size;
    $this->losers->bracket_size = $bracket_size;
  }

  /**
   * Returns the total amount of rounds in both brackets plus the finals round
   */
  public function number_of_rounds() {
    // Number of winner rounds plus number of loser rounds plus one round for finals
    return $this->winners->number_of_rounds() + $this->losers->number_of_rounds() + 1;
  }

  /**
   * Get the index of the finals round (winner of winner and loser brackets)
   */
  public function finals_round_index() {
    // Rounds are zero-indexed so final round is winners round count plus losers round count
    return $this->winners->number_of_rounds() + $this->losers->number_of_rounds();
  }

  /**
   * Get an array of round indices for winners rounds
   */
  public function winners_round_indices() {
    $indices = array();
    for ($i=0;$i<$this->winners->number_of_rounds();$i++) {
      $indices[] = $i;
    }
    return $indices;
  }

  /**
   * Get an array of round indices for losers rounds
   */
  public function losers_round_indices() {
    $indices = array();
    for ($i=0;$i<$this->losers->number_of_rounds();$i++) {
      $indices[] = $i + $this->winners->number_of_rounds();
    }
    return $indices;
  }

  /**
   * Get the data for loser advancement. Null is loser is eliminated.
   */
  public function loser_advancement($round_index, $game_index) {
    $result = array('round_index' => null, 'game_index' => null, 'player_index' => null);

    // If round is in the loser's bracket, loser of game is always eliminated
    if (in_array($round_index, $this->losers_round_indices())) {
      return null;
    }

    // Round index is just the same round index when only looking at rounds that bring in winners
    $rounds_with_new_players = array();
    foreach ($this->losers_round_indices() as $idx) {
      if ($this->losers->round_brings_in_winners($idx)) {
        $rounds_with_new_players[] = $idx;
      }
    }

    $result['round_index'] = $rounds_with_new_players[$round_index];

    // TODO: Locate game index

    // TODO: Locate player index. If round_index === 0 this is complicated because all players are losers.
    //       In subsequent rounds, loser advancement is always player position = 0

  }

  /**
   * Get the data for winner advancement
   */
  public function winner_advancement() {

    // If this is the winner's bracket, return data for a normal elimination bracket

    // If this if the winner's final, advance to finals round

    // If this is the loser's bracket, do the complicated thing

    // If this is the loser's final, advance to finals round

  }


  // TODO: Given a winners bracket index, where should the loser be placed (round & game index & game position)
  //       Beware that we need to fill from top and bottom alternating and that original seed must be transferred down to placement in loser's bracket

  //       Also beware that this should also tell you how to advance winners in the loser's bracket





}
