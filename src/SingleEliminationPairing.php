<?php namespace haugstrup\TournamentUtils;

class SingleEliminationPairing {

  public $players = array();
  public $wins = array();

  public function __construct($players, $wins) {
    $this->players = $players;
    $this->wins = $wins;

    arsort($this->wins);
  }

  public function build($round) {
    $players = $this->players;
    $groups = array('groups' => array(), 'byes' => array());
    $players_by_player_id = array_flip($players);
    $bye_count = $this->get_byes(count($players));

    if ($bye_count > 0) {
      $byes = array_slice($players, 0, $bye_count);

      foreach ($byes as $bye) {
        $this->wins[$bye] = isset($this->wins[$bye]) ? $this->wins[$bye]+1 : 1;
      }

      arsort($this->wins);

      if ($round === 0) {
        $groups['byes'] = $byes;
      }
    }

    $match_map_index = count($players)+$bye_count;
    $matches = $this->get_matches($match_map_index, $round);

    foreach ($matches as $match) {
      $opponents = array();
      foreach ($match as $c) {
        if (is_array($c)) {

          foreach ($this->wins as $player_id => $score) {

            if (in_array($players_by_player_id[$player_id]+1, $c)) {
              $opponents[] = $player_id;
              break;
            }

          }

        } else {

          // Bye are only in round one, so we can cheat here and
          // only deal with byes when $c is an int
          if (isset($players[$c-1]) && !in_array($players[$c-1], $groups['byes'])) {
            $opponents[] = $players[$c-1];
          }
        }
      }

      if ($opponents) {
        $groups['groups'][] = $opponents;
      }

    }

    return $groups;
  }

  public function get_byes($player_count) {
    $byes = array(
      2 => 0,
      4 => 0,
      6 => 2,
      8 => 0,
      10 => 6,
      12 => 4,
      14 => 2,
      16 => 0,
      20 => 12,
      22 => 10,
      24 => 8,
      26 => 6,
      28 => 4,
      30 => 2,
      32 => 0,
    );

    return isset($byes[$player_count]) ? $byes[$player_count] : null;
  }

  public function get_matches($player_count, $round) {
    $map = array(
      2 => array(
        array(
          array(1, 2),
        ),
      ),
      4 => array(
        array(
          array(1, 4),
          array(2, 3)
        ),
        array(
          array(array(1, 4), array(2, 3))
        ),
      ),
      8 => array(
        array(
          array(1, 8),
          array(2, 7),
          array(3, 6),
          array(4, 5)
        ),
        array(
          array(array(1, 8), array(4, 5)),
          array(array(2, 7), array(3, 6))
        ),
        array(
          array(array(1, 8, 4, 5), array(2, 3, 6, 7))
        ),
      ),
      16 => array(
        array(
          array(1, 16),
          array(2, 15),
          array(3, 14),
          array(4, 13),
          array(5, 12),
          array(6, 11),
          array(7, 10),
          array(8, 9)
        ),
        array(
          array(array(1, 16), array(9, 8)),
          array(array(2, 15), array(7, 10)),
          array(array(3, 14), array(6, 11)),
          array(array(4, 13), array(5, 12))
        ),
        array(
          array(array(1, 16, 9, 8), array(4, 5, 12, 13)),
          array(array(2, 15, 7, 10), array(3, 4, 11, 14))
        ),
        array(
          array(array(1, 16, 9, 8, 4, 5, 12, 13), array(2, 3, 4, 7, 10, 11, 14, 15))
        ),
      ),
      32 => array(
        array(
          array(1, 32),
          array(2, 31),
          array(3, 30),
          array(4, 29),
          array(5, 28),
          array(6, 27),
          array(7, 26),
          array(8, 25),
          array(9, 24),
          array(10, 23),
          array(11, 22),
          array(12, 21),
          array(13, 20),
          array(14, 19),
          array(15, 18),
          array(16, 17)
        ),
        array(
          array(array(1, 32), array(16, 17)),
          array(array(2, 31), array(15, 18)),
          array(array(3, 30), array(14, 19)),
          array(array(4, 29), array(13, 20)),
          array(array(5, 28), array(12, 21)),
          array(array(6, 27), array(11, 22)),
          array(array(7, 26), array(10, 23)),
          array(array(8, 25), array(9, 24))
        ),
        array(
          array(array(1, 32, 16, 17), array(8, 9, 24, 25)),
          array(array(2, 31, 15, 18), array(7, 10, 23, 26)),
          array(array(3, 30, 14, 19), array(6, 11, 22, 27)),
          array(array(4, 29, 13, 20), array(5, 12, 21, 28))
        ),
        array(
          array(array(1, 32, 16, 17, 8, 9, 24, 25), array(4, 29, 13, 20, 5, 12, 21, 28)),
          array(array(2, 31, 15, 18, 7, 10, 23, 26), array(3, 30, 14, 19, 6, 11, 22, 27))
        ),
        array(
          array(array(1,32,16,17,8,9,24,25,4,29,13,20,5,12,21,28),array(2,31,15,18,7,10,23,26,3,30,14,19,6,11,22,27))
        ),
      ),
    );

    return isset($map[$player_count]) && isset($map[$player_count][$round]) ? $map[$player_count][$round] : null;
  }

}
