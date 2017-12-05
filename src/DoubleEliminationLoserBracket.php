<?php namespace haugstrup\TournamentUtils;

require_once 'Base.php';

class DoubleEliminationLoserBracket extends Base {
  public $bracket_size = 8;

  public function __construct($bracket_size) {
    $this->bracket_size = $bracket_size;
  }

  public function number_of_rounds() {
    $round_count_winners_bracket = ceil(log($this->bracket_size)/log(2));
    return (int)($round_count_winners_bracket*2)-2;
  }

  public function round_brings_in_winners($round_index) {
    if ($round_index === 0 || $round_index%2 !== 0) {
      return true;
    }
    return false;
  }

  public function game_count_for_round($round_index) {
    $normalized_index =  $round_index%2 === 0 ? $round_index : $round_index-1;

    // Maybe just give up and make a big array?
    $games = array(
      4 => array(1, 1),
      8 => array(2, 2, 1, 1),
      16 => array(4, 4, 2, 2, 1, 1),
      32 => array(8, 8, 4, 4, 2, 2, 1, 1),
      64 => array(16, 16, 8, 8, 4, 4, 2, 2, 1, 1),
      128 => array(32, 32, 16, 16, 8, 8, 4, 4, 2, 2, 1, 1),
    );

    return $games[$this->bracket_size][$normalized_index];
  }
}
