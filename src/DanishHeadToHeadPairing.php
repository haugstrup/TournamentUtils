<?php namespace haugstrup\TournamentUtils;

class DanishHeadToHeadPairing {

  public $players = array();

  public function __construct($players) {
    $this->players = $players;
  }

  public function build() {
    $players = $this->players;
    $groups = array('groups' => array(), 'byes' => array());

    if (count($this->players)%2 != 0) {
      $groups['byes'][] = array_splice($players, -1, 1);
    }

    while (count($players) >= 2) {
      $groups['groups'][] = array_splice($players, 0, 2);
    }

    return $groups;
  }

}
