<?php namespace haugstrup\GroupBuilder;

class GroupBuilder {

  public $group_maps = array(
    5 => array(
      16 => array(16, 16, 8, 8, 4),
      20 => array(20, array(12, 8), array(12, 8), array(8, 8, 4), 4),
      24 => array(24, 12, 12, 8, 4),
      28 => array(28, array(16, 12), array(16, 12), array(8, 8, 8, 4), 4),
      32 => array(32, 16, 16, 8, 4),
      36 => array(36, array(20, 16), 12, array(8, 8, 8, 8, 4), 4),
      40 => array(40, 20, array(16, 16, 8), 8, 4),
      44 => array(44, array(24, 20), array(16, 16, 12), array(8, 8, 8, 8, 8, 4), 4),
      48 => array(48, 24, 16, 8, 4),
    ),

    10 => array(
      16 => array(16, 16, 16, 16, 8, 8, 8, 8, 8, 4),
      20 => array(20, array(12, 8), array(12, 8), array(12, 8), array(12, 8), array(8, 8, 4), array(8, 8, 4), array(8, 8, 4), array(8, 8, 4), 4),
      24 => array(24, 12, 12, 12, 12, 8, 8, 8, 8, 4),
      28 => array(28, array(16, 12), array(16, 12), array(16, 12), array(16, 12), array(8, 8, 8, 4), array(8, 8, 8, 4), array(8, 8, 8, 4), array(8, 8, 8, 4), 4),
      32 => array(32, 16, 16, 16, 16, 8, 8, 8, 8, 4),
      36 => array(36, array(20, 16), array(20, 16), 12, 12, 12, array(8, 8, 8, 8, 4), array(8, 8, 8, 8, 4), array(8, 8, 8, 8, 4), 4),
      40 => array(40, 20, 20, array(16, 16, 8), array(16, 16, 8), array(16, 16, 8), 8, 8, 8, 4),
      44 => array(44, array(24, 20), array(24, 20), array(16, 16, 12), array(16, 16, 12), array(16, 16, 12), array(8, 8, 8, 8, 8, 4), array(8, 8, 8, 8, 8, 4), array(8, 8, 8, 8, 8, 4), 4),
      48 => array(48, 24, 24, 16, 16, 16, 8, 8, 8, 4),
    ),

    // Pinball at the lake, 7 round tiers
    7 => array(
      16 => array(16, 16, 16, 16, 8, 8, 8),
      20 => array(20, 20, 20, array(12, 8), array(12, 8), array(8, 8, 4), array(8, 8, 4)),
      24 => array(24, 24, 24, 12, 12, 8, 8),
      28 => array(28, 28, 28, array(16, 12), array(16, 12), array(8, 8, 8, 4), array(8, 8, 8, 4)),
      32 => array(32, 32, 32, 16, 16, 8, 8),
      36 => array(36, array(20, 16), array(20, 16), 12, 12, array(8, 8, 8, 8, 4), array(8, 8, 8, 8, 4)),
      40 => array(40, 20, 20, array(16, 16, 8), array(16, 16, 8), 8, 8),
      44 => array(44, array(24, 20), array(24, 20), array(16, 16, 12), array(16, 16, 12), array(8, 8, 8, 8, 8, 4)),
      48 => array(48, 24, 24, 16, 16, 8, 8),
    )
  );

  public $rounds = 5;
  public $max_players = 48;
  public $players = array();

  public function __construct($rounds, $players, $options = array()) {
    $this->players = $players;
    $this->rounds = $rounds;
    if (isset($options['max_players'])) {
      $this->max_players = $options['max_players'];
    }
  }

  public function get_group_map() {
    $player_count = count($this->players);
    if (isset($this->group_maps[$this->rounds][$player_count])) {
      $map = $this->group_maps[$this->rounds][$player_count];
      $key = $player_count;
    }
    else {
      $i = $player_count;
      while ($i <= $this->max_players) {
        if (isset($this->group_maps[$this->rounds][$i])) {
          $map = $this->group_maps[$this->rounds][$i];
          $key = $i;
          break;
        }
        $i++;
      }
    }

    if (!$map) {
      throw new Exception('Couldn\'t find map');
    }

    return array('map' => $map, 'key' => $key);
  }

  // Find the specific map for a specific round
  public function get_round_map($round) {
    $map = $this->get_group_map();
    $round_map = $map['map'][$round];
    if (is_int($round_map)) {
      $round_map = array_pad(array(), $map['key']/$round_map, $round_map);
    }
    return $round_map;
  }

  // Build groups for a specific round
  public function build($round) {
    $players = $this->players;
    $player_count = count($this->players);
    $groups = array();

    // Stay within boundaries
    if ($round >= $this->rounds || $round < 0) {
      throw new Exception('Too many or too few rounds');
    }
    if ($player_count > $this->max_players || $player_count < 0) {
      throw new Exception('Too many or too few players');
    }

    $round_map = $this->get_round_map($round);

    foreach ($round_map as $index => $size) {
      $number_of_players = $size;

      // If we're in the third to last group and...
      // ...there are 9 players left
      // ...all tiers are 4
      // Then make all three player groups
      if (count($round_map)-3 == $index && count($players) == 9 && $round_map[$index] === 4) {
        $number_of_players--;
      }


      // If we're in the second to last group and...
      // ...we need to create 2 three-player groups
      // ...or we need to create 3 three-player groups
      // Then decrease the amount of players we grab for second-to-last group
      if (count($round_map)-2 == $index) {

        // Remaining players == required left players-2
        if (count($players) === ($round_map[$index]+$round_map[$index+1]-2)) {
          if ($round_map[$index+1] === 4) {
            $number_of_players--;
          }
        }

        // Remaining players == required players-3
        if (count($players) === ($round_map[$index]+$round_map[$index+1]-3)) {

          if ($round_map[$index+1] === 8) {
            $number_of_players--;
          }

          // Next round calls for 4 players
          if ($round_map[$index+1] === 4) {
            $number_of_players--;
            $number_of_players--;
          }
        }

      }

      $tier_players = array_splice($players, 0, $number_of_players);

      // Get people into groups.
      // Nb. if there's only 6 players left, create two 3 player groups
      $number_of_groups = $size/4;

      for($j=0;$j<$number_of_groups;$j++) {
        $three_player_group = count($tier_players) == 9 || count($tier_players) == 6 || count($tier_players) == 3;
        $middle_offset = ceil(count($tier_players)/2)-2;

        // Grab the first, middle and last players
        $group = array();
        $group = array_merge($group, array_splice($tier_players, 0, 1));
        $group = array_merge($group, array_splice($tier_players, $middle_offset, $three_player_group ? 1 : 2));
        $group = array_merge($group, array_splice($tier_players, -1, 1));

        $groups[] = $group;
      }

    }

    return $groups;
  }

}
