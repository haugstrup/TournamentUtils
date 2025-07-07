<?php

use PHPUnit\Framework\TestCase;

class HeadToHeadSwissPairingTest extends TestCase
{
    /**
     * Helper function, verifies basics:
     * - One bye if odd number of players.
     * - 2-player groups.
     * - Correct number of 2-player groups.
     * - Player with bye isn't in a group.
     * - No duplicate players in groups.
     */
    private function checkResults($count, $pairings)
    {
        $players = [];
        $this->assertEquals($count % 2, count($pairings['byes']));
        if ($count % 2) {
            if (! isset($players[$pairings['byes'][0]])) {
                $players[$pairings['byes'][0]] = 0;
            }
            $players[$pairings['byes'][0]]++;
        }
        $this->assertEquals(floor($count / 2), count($pairings['groups']));
        foreach ($pairings['groups'] as $group) {
            $this->assertEquals(2, count($group));
            foreach ($group as $player_id) {
                if (! isset($players[$player_id])) {
                    $players[$player_id] = 0;
                }
                $players[$player_id]++;
            }
        }

        $this->assertEquals($count, array_sum($players));
    }

    // No one should get a bye if there's an even number of players.
    public function test_no_bye_for_even_player_count()
    {
        $groups = [
            [
                'Andreas' => ['Per' => 1, 'Darren' => 1],
                'Per' => ['Matt' => 1, 'Andreas' => 1],
                'Shon' => ['Sally' => 1, 'Eric' => 1],
            ],
            [
                'Darren' => ['Andreas' => 1, 'Sally' => 1],
                'Matt' => ['Per' => 1, 'Eric' => 1],
                'Eric' => ['Shon' => 1, 'Matt' => 1],
            ],
        ];
        $byes = ['Andreas' => 1, 'Shon' => 1, 'Darren' => 1, 'Matt' => 1];
        $builder = new haugstrup\TournamentUtils\HeadToHeadSwissPairing($groups, $byes);

        for ($i = 0; $i < 100; $i++) {
            $pairings = $builder->build();
            $this->checkResults(6, $pairings);
        }
    }

    // Simulate round one of a tournament with an even number of players.
    public function test_single_group()
    {
        $groups = [
            [
                'Andreas' => [],
                'Per' => [],
                'Shon' => [],
                'Darren' => [],
                'Matt' => [],
                'Eric' => [],
            ],
        ];
        $builder = new haugstrup\TournamentUtils\HeadToHeadSwissPairing($groups);

        for ($i = 0; $i < 100; $i++) {
            $pairings = $builder->build();
            $this->checkResults(6, $pairings);
        }
    }

    // Handle a group with only one player
    public function test_single_player()
    {
        $groups = [
            [
                'Andreas' => [],
            ],
            [
                'Per' => [],
                'Shon' => [],
                'Darren' => [],
                'Matt' => [],
                'Eric' => [],
            ],
        ];
        $builder = new haugstrup\TournamentUtils\HeadToHeadSwissPairing($groups);

        for ($i = 0; $i < 100; $i++) {
            $pairings = $builder->build();
            $this->checkResults(6, $pairings);
        }
    }

    // Simulate round one of a tournament with an odd number of players.
    public function test_single_group_odd_count()
    {
        $groups = [
            [
                'Andreas' => [],
                'Per' => [],
                'Darren' => [],
                'Matt' => [],
                'Eric' => [],
            ],
        ];
        $builder = new haugstrup\TournamentUtils\HeadToHeadSwissPairing($groups);

        for ($i = 0; $i < 100; $i++) {
            $pairings = $builder->build();
            $this->checkResults(5, $pairings);
        }
    }

    // Player getting a bye shouldn't have more byes than another player.
    public function test_duplicate_bye()
    {
        $groups = [
            [
                'Andreas' => ['Per' => 1, 'Darren' => 1],
                'Per' => ['Matt' => 1, 'Andreas' => 1],
                'Shon' => ['Sally' => 1, 'Eric' => 1],
            ],
            [
                'Sally' => ['Darren' => 1, 'Shon' => 1],
                'Darren' => ['Andreas' => 1, 'Sally' => 1],
                'Matt' => ['Per' => 1, 'Eric' => 1],
                'Eric' => ['Shon' => 1, 'Matt' => 1],
            ],
        ];
        $byes = ['Andreas' => 1, 'Shon' => 1, 'Darren' => 1, 'Matt' => 1];
        $builder = new haugstrup\TournamentUtils\HeadToHeadSwissPairing($groups, $byes);

        for ($i = 0; $i < 100; $i++) {
            $pairings = $builder->build();
            $this->checkResults(7, $pairings);
            $this->assertFalse(isset($byes[$pairings['byes'][0]]),
                $pairings['byes'][0].' got an extra bye');
        }
    }

    // Ensure that a player is not dropped more than one group
    // E.g. a 0 strikes player is not paired with a 2 strikes player
    public function test_jumped_group()
    {
        $groups = [
            [
                'PlayerARank0' => [],
                'PlayerBRank0' => [],
                'PlayerCRank0' => [],
            ],
            [
                'PlayerARank1' => [],
                'PlayerBRank1' => [],
            ],
            [
                'PlayerARank2' => [],
            ],
            [
                'PlayerARank3' => [],
                'PlayerBRank3' => [],
                'PlayerCRank3' => [],
            ],
            [
                'PlayerARank4' => [],
                'PlayerBRank4' => [],
                'PlayerCRank4' => [],
            ],
            [
                'PlayerARank5' => [],
                'PlayerBRank5' => [],
                'PlayerCRank5' => [],
            ],
        ];
        $builder = new haugstrup\TournamentUtils\HeadToHeadSwissPairing($groups);

        $group_num = 0;
        global $player_group;
        $player_group = [];
        foreach ($groups as $players) {
            foreach ($players as $player => $opponents) {
                $player_group[$player] = $group_num;
            }
            $group_num++;
        }

        function minrank($cur_rank, $player)
        {
            global $player_group;

            return min($cur_rank, $player_group[$player]);
        }
        function maxrank($cur_rank, $player)
        {
            global $player_group;

            return max($cur_rank, $player_group[$player]);
        }

        for ($i = 0; $i < 100; $i++) {
            $pairings = $builder->build();

            $this->checkResults(15, $pairings);

            // The min rank of a group must be >= max of prior group.  If not,
            // a player has jumped through the prior group when making pairings.
            $prior_min = 0;
            $prior_max = 0;
            $prior_group = [];
            foreach ($pairings['groups'] as $group) {
                $this_min = array_reduce($group, 'minrank', 999);
                $this_max = array_reduce($group, 'maxrank', 0);
                $this->assertGreaterThanOrEqual($prior_max, $this_min, print_r($group, true).' jumped '.print_r($prior_group, true));
                $prior_min = $this_min;
                $prior_max = $this_max;
                $prior_group = $group;
            }
        }

    }

    // Should write a test to start with an odd number of players and run
    // through a simulated 10 strike tournament and validate constraints
    // such as:
    //   maximum buys - minimum buys <= 1 (no one get a buy until everyone has one)
    //   test for testJumpedGroup and a bye with an even group of players
}
