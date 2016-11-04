<?php namespace haugstrup\TournamentUtils;

require_once 'Base.php';

class SingleEliminationBracket extends Base {

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
      ),
      64 => array(
        32 => array(1, 64),
        33 => array(32, 33),
        34 => array(17, 48),
        35 => array(16, 49),
        36 => array(9, 56),
        37 => array(24, 41),
        38 => array(25, 40),
        39 => array(8, 57),
        40 => array(4, 61),
        41 => array(29, 36),
        42 => array(20, 45),
        43 => array(13, 52),
        44 => array(12, 53),
        45 => array(21, 44),
        46 => array(28, 37),
        47 => array(5, 60),
        48 => array(2, 63),
        49 => array(31, 34),
        50 => array(18, 47),
        51 => array(15, 50),
        52 => array(10, 55),
        53 => array(23, 42),
        54 => array(26, 39),
        55 => array(7, 58),
        56 => array(3, 62),
        57 => array(30, 35),
        58 => array(19, 46),
        59 => array(14, 51),
        60 => array(11, 54),
        61 => array(22, 43),
        62 => array(27, 38),
        63 => array(6, 59),
      ),
      128 => array(
        64 => array(1, 128),
        65 => array(64, 65),
        66 => array(32, 97),
        67 => array(33, 96),
        68 => array(16, 113),
        69 => array(49, 80),
        70 => array(17, 112),
        71 => array(48, 81),
        72 => array(8, 121),
        73 => array(57, 72),
        74 => array(25, 104),
        75 => array(40, 89),
        76 => array(9, 120),
        77 => array(56, 73),
        78 => array(24, 105),
        79 => array(41, 88),
        80 => array(4, 125),
        81 => array(61, 68),
        82 => array(29, 100),
        83 => array(36, 93),
        84 => array(13, 116),
        85 => array(52, 77),
        86 => array(20, 109),
        87 => array(45, 84),
        88 => array(5, 124),
        89 => array(60, 69),
        90 => array(28, 101),
        91 => array(37, 92),
        92 => array(12, 117),
        93 => array(53, 76),
        94 => array(21, 108),
        95 => array(44, 85),

        96 => array(2, 127),
        97 => array(63, 66),
        98 => array(31, 98),
        99 => array(34, 95),
        100 => array(15, 114),
        101 => array(50, 79),
        102 => array(18, 111),
        103 => array(47, 82),
        104 => array(7, 122),
        105 => array(58, 71),
        106 => array(26, 103),
        107 => array(39, 90),
        108 => array(10, 119),
        109 => array(55, 74),
        110 => array(23, 106),
        111 => array(42, 87),
        112 => array(3, 126),
        113 => array(62, 67),
        114 => array(30, 99),
        115 => array(35, 94),
        116 => array(14, 115),
        117 => array(51, 78),
        118 => array(19, 110),
        119 => array(46, 83),
        120 => array(6, 123),
        121 => array(59, 70),
        122 => array(27, 102),
        123 => array(38, 91),
        124 => array(11, 118),
        125 => array(54, 75),
        126 => array(22, 107),
        127 => array(43, 86),
      )
    );

    return $map[$this->bracket_size];
  }
}
