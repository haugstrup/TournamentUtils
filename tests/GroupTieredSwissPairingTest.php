<?php
use PHPUnit\Framework\TestCase;

class GroupTieredSwissPairingTest extends TestCase {

  public function testCanInitGroupMaps() {
    $sizes = array(5, 7, 10, 12, 13);
    $player_counts = array(16, 20, 24, 28, 32, 36, 40, 44, 48, 52, 56,60, 64, 68, 72, 76, 80, 84, 88, 92, 96, 100, 104, 108, 112, 116, 120, 124, 128);

    $players_list = array();
    for($i=0; $i<128; $i++) {
        $players_list[] = 'Seed #'.($i+1);
    }

    foreach ($sizes as $size) {
        foreach ($player_counts as $count) {
            $builder = new haugstrup\TournamentUtils\GroupTieredSwissPairing(array_slice($players_list, 0, $count), $count);
            // $builder->init_group_maps();

            // There should be
            // $this->assertEquals(count($groups['groups']), 4);

        }

    }
  }
}
