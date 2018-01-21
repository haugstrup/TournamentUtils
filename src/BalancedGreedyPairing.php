<?php namespace haugstrup\TournamentUtils;

require_once 'RandomOptimizer.php';

class BalancedGreedyPairing extends RandomOptimizer {

  public $group_size = 2;
  public $list = array();
  public $previously_matched = array();
  public $three_player_group_counts = array();

  public function __construct($list, $previously_matched = array(), $group_size = 2, $three_player_group_counts = array()) {
    $this->iterations = 100;
    $this->list = $list;
    $this->previously_matched = $previously_matched;
    $this->three_player_group_counts = $three_player_group_counts;
    $this->group_size = $group_size;

    if ($group_size !== 2 && $group_size !== 4) {
      throw new \Exception('Group size must be 2 or 4');
    }
  }

  public function solution($input) {
    $solution = array();

    // Shuffle players
    $ids = array_keys($input);
    shuffle($ids);
    $player_count = count($ids);

    // Find number of three player groups needed
    $num_of_three_player_groups = 0;
    if ($this->group_size === 4) {
      $num_of_three_player_groups = 4 - ($player_count%4);
      if ($num_of_three_player_groups === 4) {
        $num_of_three_player_groups = 0;
      }
    }

    // Generate array of empty groups
    $solution = [];
    // Smaller player counts are special
    if ($this->group_size === 4 && $player_count === 5) {
      $solution = [
        ['size' => 2, 'players' => [], 'cost' => 0],
        ['size' => 3, 'players' => [], 'cost' => 0],
      ];
    } elseif ($this->group_size === 4 && $player_count === 6) {
      $solution = [
        ['size' => 3, 'players' => [], 'cost' => 0],
        ['size' => 3, 'players' => [], 'cost' => 0],
      ];
    } elseif ($this->group_size === 4 && $player_count === 7) {
      $solution = [
        ['size' => 3, 'players' => [], 'cost' => 0],
        ['size' => 4, 'players' => [], 'cost' => 0],
      ];
    } elseif ($this->group_size === 4 && $player_count === 9) {
      $solution = [
        ['size' => 3, 'players' => [], 'cost' => 0],
        ['size' => 3, 'players' => [], 'cost' => 0],
        ['size' => 3, 'players' => [], 'cost' => 0],
      ];
    } elseif ($this->group_size === 4 && $player_count === 10) {
      $solution = [
        ['size' => 3, 'players' => [], 'cost' => 0],
        ['size' => 3, 'players' => [], 'cost' => 0],
        ['size' => 4, 'players' => [], 'cost' => 0],
      ];
    } elseif ($this->group_size === 4 && $player_count === 11) {
      $solution = [
        ['size' => 3, 'players' => [], 'cost' => 0],
        ['size' => 4, 'players' => [], 'cost' => 0],
        ['size' => 4, 'players' => [], 'cost' => 0],
      ];
    } else {
      for ($i = 0; $i < ceil($player_count/$this->group_size); $i++) {
        $size = ($i < $num_of_three_player_groups) ? 3 : $this->group_size;

        // Two player groups with odd number of players, first group should only have one player
        if ($this->group_size === 2 && $player_count%2 > 0 && $i === 0) {
          $size = 1;
        }

        $solution[] = [
          'size' => $size,
          'players' => [],
          'cost' => 0,
        ];
      }
    }

    foreach ($ids as $id) {
      $best_cost = 0;
      $best_cost_delta = 0;
      $best_group = null;

      foreach ($solution as $group_id => $group) {
        // Only consider group if there's still room left
        if (count($group['players']) >= $group['size']) continue;

        // Calculate added cost if player is placed in group
        $is_three_player_group = $this->group_size === 4 && ($group['size'] === 3 || $group['size'] === 2);
        $current_cost = $this->cost_for_players(array_merge($group['players'], [$id]), $is_three_player_group);
        $current_cost_delta = $current_cost - $group['cost'];

        // If this is the first group or the cost delta is better, select as "best"group
        if ($best_group === null || $current_cost_delta <= $best_cost_delta) {
          $best_group = $group_id;
          $best_cost = $current_cost;
          $best_cost_delta = $current_cost_delta;

          // If the current cost is zero, no need to look at more groups
          // No other group can do better
          if ($current_cost === 0) {
            break;
          }
        }
      }

      $solution[$best_group]['players'][] = $id;
      $solution[$best_group]['cost'] = $best_cost;
    }

    return $solution;
  }

  /**
   * Calculate cost for a single group of players
   *
   * @param array $players - Array of player ids
   * @param boolean $three_player_group - true is this is a three player group
   * @return void
   */
  public function cost_for_players($players, $three_player_group = false) {
    $cost = 0;

    // Add three player group costs
    if ($three_player_group) {
      foreach ($players as $id) {
        if (isset($this->three_player_group_counts[$id])) {
          // Three player group cost should be twice as bad as a repeated opponent
          $cost += pow($this->three_player_group_counts[$id]*2, 2);
        }
      }
    }

    // Add repeat opponent costs
    $handled_players = [];
    foreach ($players as $id) {
      $cost_was_added = false;
      $opponent_counts = [];
      if (isset($this->previously_matched[$id])) {
        $opponent_counts = array_count_values($this->previously_matched[$id]);
      }
      foreach ($players as $inner_id) {
        if ($id === $inner_id) continue;
        if (in_array($inner_id, $handled_players)) continue;

        if (array_key_exists($inner_id, $opponent_counts)) {
          $cost += pow($opponent_counts[$inner_id], 2);
          $cost_was_added = true;
        }
      }

      // If are assigning a repeat opponent (cost_was_added)
      // and this player has other previously matched opponents.
      // Then add to cost for each previously matched opponent
      // that was faced more than once.
      // This is to avoid one player getting multiple repeat
      // opponents before other players get their first repeat.
      if ($cost_was_added && count($opponent_counts) > 0) {
        foreach ($opponent_counts as $inner_id => $count) {
          if (!in_array($inner_id, $players)) {
            $cost += $count;
          }
        }
      }

      $handled_players[] = $id;
    }
    return $cost;
  }

  public function cost($solution) {
    $cost = 0;
    foreach ($solution as $group) {
      $cost += $group['cost'];
    }
    return $cost;
  }

  public function build() {
    $result = $this->solve($this->list);

    $groups = array();
    foreach ($result['solution'] as $matchup) {
      $group = array();

      foreach ($matchup['players'] as $id) {
        $group[] = $this->list[$id];
      }

      $groups[] = $group;
    }

    // Reverse groups here so three-player groups are placed at the end
    $groups = array_reverse($groups);
    return array('cost' => $result['cost'], 'groups' => $groups);
  }

}
