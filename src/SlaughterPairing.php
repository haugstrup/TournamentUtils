<?php namespace haugstrup\TournamentUtils;

class SlaughterPairing {

  public $players = array();
  public $group_size = 2;

  public function __construct($players, $group_size = 2) {
    $this->players = $players;
    $this->group_size = $group_size;
  }

  public function build() {
    $players = $this->players;
    $groups = array('groups' => array(), 'byes' => array());

    if ($this->group_size === 4) {

      while (count($players) > 0) {
        $count = count($players);
        $middle_offset = ceil($count/2)-2;
        $three_player_group = ($count < 10 && $count%4 != 0 && $count !== 7) ? true : false;

        $group = array();
        $group = array_merge($group, array_splice($players, 0, 1));
        $group = array_merge($group, array_splice($players, $middle_offset, $three_player_group ? 1 : 2));
        $group = array_merge($group, array_splice($players, -1, 1));

        $groups['groups'][] = $group;
      }

    } else {

      if (count($this->players)%2 != 0) {
        $bye_slice = array_splice($players, -1, 1);
        $groups['byes'][] = $bye_slice[0];
      }

      while (count($players) >= 2) {
        $group = array();
        $player_one_slice = array_splice($players, 0, 1);
        $player_two_slice = array_splice($players, -1, 1);
        $group[] = $player_one_slice[0];
        $group[] = $player_two_slice[0];
        $groups['groups'][] = $group;
      }
    }

    return $groups;
  }

}
