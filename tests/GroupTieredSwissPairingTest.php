<?php

use PHPUnit\Framework\TestCase;

class GroupTieredSwissPairingTest extends TestCase
{
    public function test_maps()
    {
        $rounds = 5;
        $players = ['Seed#1', 'Seed#2', 'Seed#3', 'Seed#4', 'Seed#5', 'Seed#6', 'Seed#7', 'Seed#8', 'Seed#9', 'Seed#10', 'Seed#11', 'Seed#12', 'Seed#13', 'Seed#14', 'Seed#15', 'Seed#16'];
        $builder = new haugstrup\TournamentUtils\GroupTieredSwissPairing($rounds, $players);

        $maps = $builder->group_maps;

        foreach ($maps as $roundCount => $round) {
            // print "Round: " . $roundCount . "\n";
            foreach ($round as $playerCount => $tiers) {
                // print "Player count: " . $playerCount . "\n";
                // Number of tiers must match number of rounds
                $this->assertEquals(count($tiers), $roundCount);
                foreach ($tiers as $tier) {
                    if (is_array($tier)) {
                        // print "Tier: " . join(',', $tier) . ": " . array_sum($tier) . ":" . $playerCount . "\n";
                        // If an array the sum of the numbers must equal the player count
                        $this->assertEquals(array_sum($tier), $playerCount);
                        // ...and each of the entries must by evenly divided by 4
                        foreach ($tier as $number) {
                            $this->assertEquals($number % 4, 0);
                        }
                    } else {
                        // print "Tier: " . $tier . "\n";
                        // If not an array the number must be:
                        // * Able to be evenly divided by 4
                        // * Able to be evenly divided by player count
                        $this->assertEquals($tier % 4, 0);
                        $this->assertEquals($playerCount % $tier, 0);
                    }
                }
            }
        }
    }
}
