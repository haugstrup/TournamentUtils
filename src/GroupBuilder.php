<?php namespace haugstrup\GroupBuilder;

// TODO: Use different/accelerated group_map for < 6 rounds

class GroupBuilder {
  public $group_map = array(
    16 => array(16, 16, 16, 16, 8, 8, 8),
    20 => array(20, 20, 20, array(12, 8), array(12, 8), array(8, 8, 4), array(8, 8, 4)),
    24 => array(24, 24, 24, 12, 12, 8, 8),
    28 => array(28, 28, 28, array(16, 12), array(16, 12), array(8, 8, 8, 4), array(8, 8, 8, 4)),
    36 => array(36, array(20, 16), array(20, 16), 12, 12, array(8, 8, 8, 8, 4), array(8, 8, 8, 8, 4)),
    40 => array(40, 20, 20, array(16, 16, 8), array(16, 16, 8), 8, 8),
    44 => array(44, array(24, 20), array(24, 20), array(16, 16, 12), array(16, 16, 12), array(8, 8, 8, 8, 8, 4)),
    48 => array(48, 24, 24, 16, 16, 8, 8),
  );
  public $max_rounds = 7;
  public $max_players = 48;
  public $players = array();

  public function __construct($players, $options = array()) {
    $this->players = $players;
    if (isset($options['max_rounds'])) {
      $this->max_rounds = $options['max_rounds'];
    }
    if (isset($options['max_players'])) {
      $this->max_players = $options['max_players'];
    }
  }

  public function get_group_map() {
    $player_count = count($this->players);
    if (isset($this->group_map[$player_count])) {
      $map = $this->group_map[$player_count];
      $key = $player_count;
    }
    else {
      $i = $player_count;
      while ($i <= $this->max_players) {
        if (isset($this->group_map[$i])) {
          $map = $this->group_map[$i];
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
    if ($round >= $this->max_rounds || $round < 0) {
      throw new Exception('Too many or too few rounds');
    }
    if ($player_count > $this->max_players || $player_count < 0) {
      throw new Exception('Too many or too few players');
    }

    $round_map = $this->get_round_map($round);

    foreach ($round_map as $index => $size) {
      $number_of_players = $size;

      // If we're in the second two last group and...
      // ...we need to create 2 three-player groups
      // ...or we need to create 3 three-player groups
      // Then decrease the amount of players we grab for second-to-last group
      if (count($round_map)-2 == $index) {

        if (count($players) === ($round_map[$index]+$round_map[$index+1]-2)) {
          $number_of_players--;
        }

        if (count($players) === ($round_map[$index]+$round_map[$index+1]-3)) {
          $number_of_players--;
          if ($round_map[$index+1] === 4) {
            $number_of_players--;
          }
        }

      }

      $tier_players = array_splice($players, 0, $number_of_players);

      // Get people into groups.
      // Nb. if there's only 6 players left, create two 3 player groups
      $number_of_groups = $size/4;

      for($j=0;$j<$number_of_groups;$j++) {
        $three_player_group = count($tier_players) == 6 || count($tier_players) == 3;
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
