<?php
use PHPUnit\Framework\TestCase;

class Arena {
  public function __construct($id, $count = 0) {
    $this->id = $id;
    $this->count = $count;
  }
  public function getArenaId() {
    return $this->id;
  }
  public function getArenaCount() {
    return $this->count;
  }
}

class ArenaSelectorTest extends TestCase {

  public function testChoosesUnplayedArenas() {
    $arena_counts = array('Pinbot' => 1, 'Paragon' => 4, 'Embryon' => 4, 'Rollergames' => 1);
    // Prep list of available arenas
    $available_arenas = array(new Arena('Pinbot'), new Arena('Rollergames'), new Arena('Paragon'), new Arena('Embryon'), new Arena('Scorpion'), new Arena('Black Pyramid'));
    // Init and run selector
    $selector = new haugstrup\TournamentUtils\ArenaSelector($arena_counts, $available_arenas);
    $arena = $selector->select();

    $this->assertThat(
      $arena,
      $this->logicalXor(
        $this->equalTo(new Arena('Scorpion')),
        $this->equalTo(new Arena('Black Pyramid'))
      )
    );
  }


  public function testChoosesLeastPlayedArenas() {
    $arena_counts = array('Pinbot' => 1, 'Paragon' => 4, 'Embryon' => 4, 'Rollergames' => 1);
    // Prep list of available arenas
    $available_arenas = array(new Arena('Pinbot'), new Arena('Rollergames'), new Arena('Paragon'), new Arena('Embryon'));
    // Init and run selector
    $selector = new haugstrup\TournamentUtils\ArenaSelector($arena_counts, $available_arenas);
    $arena = $selector->select();

    $this->assertThat(
      $arena,
      $this->logicalXor(
        $this->equalTo(new Arena('Pinbot')),
        $this->equalTo(new Arena('Rollergames'))
      )
    );
  }
}
