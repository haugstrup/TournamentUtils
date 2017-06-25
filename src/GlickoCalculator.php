<?php namespace haugstrup\TournamentUtils;

/**
 * Glicko1 calculator. See: http://glicko.net/glicko/glicko.pdf
 */
class GlickoCalculator {

  private $default_rating = null;
  private $default_rd = null;
  private $c = null;
  private $rd_min = null;
  private $results = [];
  private $players = [];

  /**
   * Get the q constant
   *
   * @return float
   */
  static function q() {
    return log(10)/400;
  }

  /**
   * Calculate g value based on RD
   *
   * @param float $rd
   * @return float
   */
  static function g($rd) {
    $q = GlickoCalculator::q();
    return 1/sqrt((1+(3*$q*$q*$rd*$rd)/(pi() * pi())));
  }

  /**
   * Calculate E based on player ratings and opponent RD
   *
   * @param float $rating
   * @param float $opponent_rating
   * @param float $opponent_rd
   * @return float
   */
  static function E($rating, $opponent_rating, $opponent_rd) {
    $g = GlickoCalculator::g($opponent_rd);
    return round(1/(1+pow(10,($g*($opponent_rating - $rating)/400))), 4);
  }

  /**
   * Create new calculator
   *
   * @param integer $default_rating
   * @param integer $default_rd
   * @param float $c
   * @param integer $rd_min
   * @return void
   */
  public function __construct($default_rating = 1500, $default_rd = 350, $c = 14.2694, $rd_min = 30) {
    $this->default_rating = $default_rating;
    $this->default_rd = $default_rd;
    $this->c = $c;
    $this->rd_min = $rd_min;
  }

  /**
   * Add player object to the calculator
   *
   * @param array $players - Array of player hashes keyed by ID. Each player should have `rating` and `rd` provided
   * @return void
   */
  public function addPlayers($players) {
    foreach ($players as $id => $player) {
      $this->players[$id] = $player;
    }
  }

  /**
   * Add result to calculator.
   *
   * @param array $result - Player IDs in order of finishing position
   * @param [integer] $group_size - Optional, size of group that played game
   * @return void
   */
  public function addResult($result = [], $group_size = null) {
    if (count($result) < 2) return;

    $group_size = $group_size ? $group_size : count($result);
    $adjustment = null;
    if ($group_size > 2) {
      $adjustment = sqrt($group_size-1);
    }

    if (count($result) === 2) {
      $this->results[$result[0]][] = [
        'outcome' => 1,
        'opponent' => $result[1],
        'adjustment' => $adjustment,
      ];
      $this->results[$result[1]][] = [
        'outcome' => 0,
        'opponent' => $result[0],
        'adjustment' => $adjustment,
      ];
    } else {
      $handled_players = [];
      foreach ($result as $i => $player) {
        foreach ($result as $j => $current_player) {
          if ($player === $current_player) continue;
          if (in_array($current_player, $handled_players)) continue;

          $this->results[$player][] = [
            'outcome' => $i < $j ? 1 : 0,
            'opponent' => $current_player,
            'adjustment' => $adjustment,
          ];
          $this->results[$current_player][] = [
            'outcome' => $j < $i ? 1 : 0,
            'opponent' => $player,
            'adjustment' => $adjustment,
          ];
        }
        $handled_players[] = $player;
      }
    }
  }

  public function addDraw($result = [], $group_size = null) {
      if (count($result) < 2) return;

      $group_size = $group_size ? $group_size : count($result);
      $adjustment = null;
      if ($group_size > 2) {
        $adjustment = sqrt($group_size-1);
      }

      $this->results[$result[0]][] = [
        'outcome' => 0.5,
        'opponent' => $result[1],
        'adjustment' => $adjustment,
      ];
      $this->results[$result[1]][] = [
        'outcome' => 0.5,
        'opponent' => $result[0],
        'adjustment' => $adjustment,
      ];
  }

  /**
   * Get results.
   *
   * @return array
   */
  public function getResults() {
    return $this->results;
  }

  /**
   * Calculate new RD at start of period (or if a player didn't participate)
   *
   * @param float $rd
   * @param integer $periods - Number of times to advance
   * @return float
   */
  public function advanceRD($rd, $periods = 1) {
    for ($i=0;$i<$periods;$i++) {
      if ($rd >= $this->default_rd) return $this->default_rd;
      $rd = min(sqrt($rd*$rd + $this->c*$this->c), $this->default_rd);
    }
    return $rd;
  }

  /**
   * Calculate the new RD for a player based on the results added
   *
   * @param integer $player - Player id
   * @return float
   */
  public function calculateNewRDForPlayer($player) {
    $q = GlickoCalculator::q();
    $onset_rd = $this->players[$player]['rd'];

    if (!isset($this->results[$player])) return 0;

    $sigma = 0;
    foreach ($this->results[$player] as $result) {
      $g = GlickoCalculator::g($this->players[$result['opponent']]['rd']);
      $e = GlickoCalculator::E($this->players[$player]['rating'], $this->players[$result['opponent']]['rating'], $this->players[$result['opponent']]['rd']);

      if (!$result['adjustment']) {
        $sigma += $g * $g * $e * (1 - $e);
      } else {
        $sigma += ($g * $g * $e * (1 - $e))/$result['adjustment'];
      }
    }

    $d_squared = 0;
    if ($sigma) {
      $d_squared = 1 / ($q * $q * $sigma);
    }

    if ($d_squared) {
      return min(max(sqrt(1/(1/($onset_rd * $onset_rd) + 1 / $d_squared)), $this->rd_min),$this->default_rd);
    }
    return $onset_rd;
  }

  /**
   * Calculate the new rating for a player based on the results
   *
   * @param integer $player - Playerid
   * @param float $new_rd - The new RD for this player (from `calculateNewRDForPlayer`)
   * @return float
   */
  public function calculateNewRatingForPlayer($player, $new_rd) {
    $q = GlickoCalculator::q();
    $sigma = 0;
    foreach ($this->results[$player] as $result) {
      $g = GlickoCalculator::g($this->players[$result['opponent']]['rd']);
      $e = GlickoCalculator::E($this->players[$player]['rating'], $this->players[$result['opponent']]['rating'], $this->players[$result['opponent']]['rd']);

      if (empty($result['adjustment'])) {
        $sigma += $g * ($result['outcome'] - $e);
      } else {
        $sigma += ($g * ($result['outcome'] - $e))/$result['adjustment'];
      }
    }
    return $this->players[$player]['rating'] + $q * $new_rd * $new_rd * $sigma;
  }

  /**
   * Update ratings and RD for all players. Modifies $this->players
   *
   * @return array - New list of players
   */
  public function updateRatings() {
    // Step 1: Increase RD compared to last period for all players
    foreach ($this->players as $id => $player) {
      $this->players[$id]['rd'] = $this->advanceRD($player['rd']);
    }

    // Step 2. Calculate new rating & RD for players
    foreach ($this->players as $id => $player) {
      if (isset($this->results[$id])) {
        $this->players[$id]['new_rd'] = $this->calculateNewRDForPlayer($id);
        $this->players[$id]['new_rating'] = $this->calculateNewRatingForPlayer($id, $this->players[$id]['new_rd']);
      } else{
        $this->players[$id]['new_rd'] = $this->players[$id]['rd'];
        $this->players[$id]['new_rating'] = $this->players[$id]['rating'];
      }
    }

    return $this->players;
  }
}
