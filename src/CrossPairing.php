<?php namespace haugstrup\TournamentUtils;

class CrossPairing {

  public $players = array();

  public function __construct($players) {
    $this->players = $players;
  }

  public function build() {
    $players = $this->players;
    $groups = array('groups' => array(), 'byes' => array());

    if (count($this->players)%2 != 0) {
      $bye_slice = array_splice($players, -1, 1);
      $groups['byes'][] = $bye_slice[0];
    }

    $split = array_chunk($players, count($players)/2);

    foreach ($split[0] as $index => $player) {
      $groups['groups'][] = array($player, $split[1][$index]);
    }

    return $groups;
  }

}
