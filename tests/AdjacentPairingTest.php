<?php

use PHPUnit\Framework\TestCase;

class AdjacentPairingTest extends TestCase
{
    public function test_can_pair_head_to_head()
    {
        $players = ['Seed#1', 'Seed#2', 'Seed#3', 'Seed#4', 'Seed#5', 'Seed#6', 'Seed#7', 'Seed#8'];
        $builder = new haugstrup\TournamentUtils\AdjacentPairing($players, 2);
        $groups = $builder->build();

        $this->assertEquals(count($groups['groups']), 4);
        $this->assertEquals(count($groups['byes']), 0);
        $this->assertEquals($groups['groups'][0], ['Seed#1', 'Seed#2']);
        $this->assertEquals($groups['groups'][1], ['Seed#3', 'Seed#4']);
        $this->assertEquals($groups['groups'][2], ['Seed#5', 'Seed#6']);
        $this->assertEquals($groups['groups'][3], ['Seed#7', 'Seed#8']);
    }
}
