<?php namespace haugstrup\TournamentUtils;

class SingleEliminationBracket {

  // This class relies on a binary heap as described on: http://joenoodles.com/2013/3/2013-bracket-design

  public $players = array();

  public function __construct($bracket_size, $players, $winners_by_heap_index) {
    $this->bracket_size = $bracket_size;
    $this->players = $players;
    $this->winners = $winners_by_heap_index;
  }

  public function children($parent_index) {
    return array($parent_index<<1, ($parent_index<<1)+1);
  }

  public function parent($child_index) {
    return $child_index>>1;
  }

  public function game_count() {
    return $this->bracket_size-1;
  }

  public function round($index) {
    $n = -1;
    while($index > 0) {
        $index >>= 1;
        $n++;
    }
    return $n;
  }

  public function indexes_in_round($round) {
    $list = array();

    for($i=1;$i<=$this->game_count();$i++) {
      if ($this->round($i) === $round) {
        $list[] = $i;
      }
    }

    return $list;
  }

  public function number_of_rounds() {
    return $this->round($this->game_count())+1;
  }

  // Looks at winners of previous games to determine who should play
  public function opponents_for_index($index) {
    $opponents = array();

    // If $index indicates first round, use first_round groups to determine opponents
    if ($this->round($index) === $this->number_of_rounds()) {
      $groups = $this->first_round_groups();
      foreach ($groups[$index] as $seed) {

        // Subtract one since players are zero-indexed and seeds are not
        if (!empty($this->players[$seed-1])) {
          $opponents[] = $this->players[$seed-1];
        }
      }

    } else {
      $children = $this->children($index);
      foreach ($children as $child) {
        if (!empty($this->winners[$child])) {
          foreach ($this->players as $player) {
            if ($player === $this->winners[$child]) {
              $opponents[] = $player;
            }
          }
        }
      }
    }

    return $opponents;
  }

  // Pairs of opponents for the first round
  // Numeric keys refer to the heap index for that game
  public function first_round_groups() {
    $map = array(
      2 => array(1 => array(1, 2)),
      4 => array(2 => array(1, 4), 3 => array(2, 3)),
      8 => array(4 => array(1, 8), 5 => array(4, 5), 6 => array(3, 6), 7 => array(2, 7)),
      16 => array(
        8 => array(1, 16),
        9 => array(8, 9),
        10 => array(4, 13),
        11 => array(5, 12),
        12 => array(2, 15),
        13 => array(7, 10),
        14 => array(3, 14),
        15 => array(6, 11)
      ),
      32 => array(
        16 => array(1, 32),
        17 => array(16, 17),
        18 => array(9, 24),
        19 => array(8, 25),
        20 => array(4, 29),
        21 => array(13, 20),
        22 => array(12, 21),
        23 => array(5, 28),
        24 => array(2, 31),
        25 => array(15, 18),
        26 => array(10, 23),
        27 => array(7, 26),
        28 => array(3, 30),
        29 => array(14, 19),
        30 => array(11, 22),
        31 => array(6, 27)
      )
    );

    return $map[$this->bracket_size];
  }
}
