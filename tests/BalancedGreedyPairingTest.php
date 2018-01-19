<?php
use PHPUnit\Framework\TestCase;

class BalancedGreedyPairingTest extends TestCase {

    public function testCreatesCorrectGroupCounts() {
        $group_counts_2 = [
            2 => 1,
            3 => 2,
            4 => 2,
            5 => 3,
            6 => 3,
            7 => 4,
            8 => 4,
            9 => 5,
            10 => 5,
            11 => 6,
            12 => 6,
            13 => 7,
            14 => 7,
            15 => 8,
            16 => 8,
            17 => 9,
            18 => 9,
            19 => 10,
            20 => 10,
        ];
        $group_counts_4 = [
            4 => 1,
            5 => 2,
            6 => 2,
            7 => 2,
            8 => 2,
            9 => 3,
            10 => 3,
            11 => 3,
            12 => 3,
            13 => 4,
            14 => 4,
            15 => 4,
            16 => 4,
            17 => 5,
            18 => 5,
            19 => 5,
            20 => 5,
        ];

        $players = [];
        for($i=0;$i<20;$i++) {
            $players[] = 'Seed#'.($i+1);
        }
        // Two player groups
        for($i=2;$i<21;$i++) {
            $list = array_slice($players, 0, $i);
            $builder = new haugstrup\TournamentUtils\BalancedGreedyPairing($list, [], 2, []);
            $solution = $builder->solution($list);
            $this->assertEquals($group_counts_2[$i], count($solution));
        }
        // Four player groups
        for($i=4;$i<21;$i++) {
            $list = array_slice($players, 0, $i);
            $builder = new haugstrup\TournamentUtils\BalancedGreedyPairing($list, [], 4, []);
            $solution = $builder->solution($list);
            $this->assertEquals($group_counts_4[$i], count($solution));
        }
    }

    public function testCreatesCorrectGroupSizes() {
        $players = [];
        for($i=0;$i<20;$i++) {
            $players[] = 'Seed#'.($i+1);
        }
        // Two player groups
        for($i=2;$i<21;$i++) {
            $list = array_slice($players, 0, $i);
            $builder = new haugstrup\TournamentUtils\BalancedGreedyPairing($list, [], 2, []);
            $solution = $builder->solution($list);
            $sum = 0;
            foreach ($solution as $group) {
                $sum += $group['size'];
            }
            $this->assertEquals(count($list), $sum);
        }
        // Four player groups
        for($i=4;$i<21;$i++) {
            $list = array_slice($players, 0, $i);
            $builder = new haugstrup\TournamentUtils\BalancedGreedyPairing($list, [], 4, []);
            $solution = $builder->solution($list);
            $sum = 0;
            foreach ($solution as $group) {
                $sum += $group['size'];
            }
            $this->assertEquals(count($list), $sum);
        }
    }

    public function testCreatesGroupsWithCorrectPlayerCounts() {
        $players = [];
        for($i=0;$i<20;$i++) {
            $players[] = 'Seed#'.($i+1);
        }
        // Two player groups
        for($i=2;$i<21;$i++) {
            $list = array_slice($players, 0, $i);
            $builder = new haugstrup\TournamentUtils\BalancedGreedyPairing($list, [], 2, []);
            $solution = $builder->solution($list);
            $sum = 0;
            foreach ($solution as $group) {
                $sum += count($group['players']);
            }
            $this->assertEquals(count($list), $sum);
        }
        // Four player groups
        for($i=4;$i<21;$i++) {
            $list = array_slice($players, 0, $i);
            $builder = new haugstrup\TournamentUtils\BalancedGreedyPairing($list, [], 4, []);
            $solution = $builder->solution($list);
            $sum = 0;
            foreach ($solution as $group) {
                $sum += count($group['players']);
            }
            $this->assertEquals(count($list), $sum);
        }
    }

    public function testCreatesCorrectTwoPlayerGroupCounts() {
        $group_counts_2 = [
            2 => 1,
            3 => 1,
            4 => 2,
            5 => 2,
            6 => 3,
            7 => 3,
            8 => 4,
            9 => 4,
            10 => 5,
            11 => 5,
            12 => 6,
            13 => 6,
            14 => 7,
            15 => 7,
            16 => 8,
            17 => 8,
            18 => 9,
            19 => 9,
            20 => 10,
        ];
        $group_counts_4 = [
            4 => 0,
            5 => 1,
            6 => 0,
            7 => 0,
            8 => 0,
            9 => 0,
            10 => 0,
            11 => 0,
            12 => 0,
            13 => 0,
            14 => 0,
            15 => 0,
            16 => 0,
            17 => 0,
            18 => 0,
            19 => 0,
            20 => 0,
        ];

        $players = [];
        for($i=0;$i<20;$i++) {
            $players[] = 'Seed#'.($i+1);
        }
        // Two player groups
        for($i=2;$i<21;$i++) {
            $list = array_slice($players, 0, $i);
            $builder = new haugstrup\TournamentUtils\BalancedGreedyPairing($list, [], 2, []);
            $solution = $builder->solution($list);

            $count = 0;
            foreach ($solution as $group) {
                if ($group['size'] === 2) {
                    $count++;
                }
            }
            $this->assertEquals($group_counts_2[$i], $count);
        }
        // Four player groups
        for($i=4;$i<21;$i++) {
            $list = array_slice($players, 0, $i);
            $builder = new haugstrup\TournamentUtils\BalancedGreedyPairing($list, [], 4, []);
            $solution = $builder->solution($list);

            $count = 0;
            foreach ($solution as $group) {
                if ($group['size'] === 2) {
                    $count++;
                }
            }
            $this->assertEquals($group_counts_4[$i], $count);
        }
    }

    public function testCreatesCorrectThreePlayerGroupCounts() {
        $group_counts = [
            4 => 0,
            5 => 1,
            6 => 2,
            7 => 1,
            8 => 0,
            9 => 3,
            10 => 2,
            11 => 1,
            12 => 0,
            13 => 3,
            14 => 2,
            15 => 1,
            16 => 0,
            17 => 3,
            18 => 2,
            19 => 1,
            20 => 0,
        ];

        $players = [];
        for($i=0;$i<20;$i++) {
            $players[] = 'Seed#'.($i+1);
        }
        for($i=4;$i<21;$i++) {
            $list = array_slice($players, 0, $i);
            $builder = new haugstrup\TournamentUtils\BalancedGreedyPairing($list, [], 4, []);
            $solution = $builder->solution($list);

            $count = 0;
            foreach ($solution as $group) {
                if ($group['size'] === 3) {
                    $count++;
                }
            }
            $this->assertEquals($group_counts[$i], $count);
        }
    }

    public function testCalculatesCostWithNoPreviousMatches() {
        $players = [];
        for($i=0;$i<20;$i++) {
            $players[] = 'Seed#'.($i+1);
        }
        $builder = new haugstrup\TournamentUtils\BalancedGreedyPairing($players, [], 4, []);
        $cost = $builder->cost_for_players(array_slice($players, 0, 4));
        $this->assertEquals(0, $cost);
    }

    public function testCalculatesCostWithPreviousMatches() {
        $players = [];
        for($i=0;$i<20;$i++) {
            $players[] = 'Seed#'.($i+1);
        }

        $previously_matched = [
            0 => [1, 2, 3],
            1 => [0, 2],
            2 => [0, 1],
            3 => [0],
        ];

        $builder = new haugstrup\TournamentUtils\BalancedGreedyPairing($players, $previously_matched, 4, []);
        $cost = $builder->cost_for_players(array_keys((array_slice($players, 0, 4))), false);
        $this->assertEquals(4, $cost);
    }

    public function testCalculatesCostWithRepeatPreviousMatches() {
        $players = [];
        for($i=0;$i<20;$i++) {
            $players[] = 'Seed#'.($i+1);
        }

        $previously_matched = [
            0 => [1, 2, 3, 1],
            1 => [0, 2, 0, 2],
            2 => [0, 1, 1],
            3 => [0],
        ];
        $builder = new haugstrup\TournamentUtils\BalancedGreedyPairing($players, $previously_matched, 4, []);
        $cost = $builder->cost_for_players(array_keys(array_slice($players, 0, 4)), false);
        $this->assertEquals(10, $cost);
    }

    public function testCalculatesCostWithThreePlayerMatches() {
        $players = [];
        for($i=0;$i<20;$i++) {
            $players[] = 'Seed#'.($i+1);
        }

        $previously_matched = [
            0 => [1, 2, 3, 1],
            1 => [0, 2, 0, 2],
            2 => [0, 1, 1],
            3 => [0],
        ];
        $three_player_matches = [
            1 => 1,
            2 => 2,
        ];
        // Has three player matches array, but this isn't a three player group
        $builder = new haugstrup\TournamentUtils\BalancedGreedyPairing($players, $previously_matched, 4, $three_player_matches);
        $cost = $builder->cost_for_players(array_keys(array_slice($players, 0, 3)), false);
        $this->assertEquals($cost, 9);
        // Actual three player group
        $builder = new haugstrup\TournamentUtils\BalancedGreedyPairing($players, $previously_matched, 4, $three_player_matches);
        $cost = $builder->cost_for_players(array_keys(array_slice($players, 0, 3)), true);
        $this->assertEquals(29, $cost);
    }

    public function testSolutionCalculatesCost() {
        $players = [];
        for($i=0;$i<4;$i++) {
            $players[] = 'Seed#'.($i+1);
        }

        $previously_matched = [
            0 => [1, 2, 3],
            1 => [0, 2],
            2 => [0, 1],
            3 => [0],
        ];

        $builder = new haugstrup\TournamentUtils\BalancedGreedyPairing($players, $previously_matched, 4, []);
        $solution = $builder->solution($players);
        $this->assertEquals(4, $solution[0]['cost']);
    }

    public function testCostCalculatesCost() {
        $players = [];
        for($i=0;$i<8;$i++) {
            $players[] = 'Seed#'.($i+1);
        }

        $previously_matched = [
            0 => [1, 2, 3, 4, 5, 6, 7],
            1 => [0, 2, 3, 4, 5, 6, 7],
            2 => [0, 1, 3, 4, 5, 6, 7],
            3 => [0, 1, 2, 4, 5, 6, 7],
            4 => [0, 1, 2, 3, 5, 6, 7],
            5 => [0, 1, 2, 3, 4, 6, 7],
            6 => [0, 1, 2, 3, 4, 5, 7],
            7 => [0, 1, 2, 3, 4, 5, 6],
        ];

        $builder = new haugstrup\TournamentUtils\BalancedGreedyPairing($players, $previously_matched, 4, []);
        $solution = $builder->solution($players);
        $cost = $builder->cost($solution);
        $this->assertEquals(12, $cost);
    }

    public function testBuilderCanBuild() {
        $players = [];
        for($i=0;$i<4;$i++) {
            $players[] = 'Seed#'.($i+1);
        }

        $previously_matched = [
            0 => [1, 2, 3],
            1 => [0, 2],
            2 => [0, 1],
            3 => [0],
        ];

        $builder = new haugstrup\TournamentUtils\BalancedGreedyPairing($players, $previously_matched, 4, []);
        $result = $builder->build();
        $this->assertTrue(isset($result['cost']));
        $this->assertTrue(isset($result['groups']));
        $this->assertEquals(4, $result['cost']);
        $this->assertEquals(1, count($result['groups']));
    }

    public function testBuilderCanBuildAcceptable16PlayerGroups() {
        $players = [];
        for($i=0;$i<16;$i++) {
            $players[] = 'Seed#'.($i+1);
        }

        // Run this simulation 100 times
        for ($j=0;$j<100;$j++) {

            $previously_matched = [];
            // Simulate 5 rounds of play
            for ($i=0;$i<5;$i++) {
                $builder = new haugstrup\TournamentUtils\BalancedGreedyPairing($players, $previously_matched, 4, []);
                $result = $builder->build();

                $this->assertTrue(isset($result['cost']));
                $this->assertTrue(isset($result['groups']));
                // First three rounds must have zero cost
                // Last round can have 0 or 4 cost
                if ($i < 3) {
                    $this->assertEquals(0, $result['cost']);
                } else {
                    $this->assertLessThan(5, $result['cost']);
                }
                $this->assertEquals(4, count($result['groups']));

                // Add matchups to $previously_matched
                foreach ($result['groups'] as $group) {
                    foreach ($group as $player) {
                        foreach ($group as $c_player) {
                            if ($player !== $c_player) {
                                $id = array_search ($player, $players);
                                $c_id = array_search ($c_player, $players);
                                $previously_matched[$id][] = $c_id;
                            }
                        }
                    }
                }
            }

        }
    }

    public function testBuilderCanBuildPerfectSFPDSeason() {
        $attendance = json_decode("[[14757,14817,14743,14741,14739,14807,14754,14744,14810,14770,14740,14775,14788,14787,14756,14794,14755,14815,14746,14819,14813,14793,14762,14769,14821,14748,14798,14801,14768,14745,14796,14811,14789,14804,24495,14774,24500,14752,14759,14783,14806,14805,14773,14751,14753,14827,14785,14782,24496,14776,14742,14816,20204,24493,14767,14780,14781,14797,24497],[14742,14754,14761,14780,14743,14767,14813,14825,14749,14751,14788,17306,14752,14776,14805,14823,14757,14774,14797,40836,14766,14787,14806,14821,14741,14768,14781,14784,14775,14783,14789,26854,14755,14785,20204,24500,14748,14760,14796,24501,14753,14808,14816,24491,14770,14807,14819,16574,14798,14817,14827,24497,14744,14746,14811,16571,14747,14769,14771,14815,14745,14756,14814,27828,14740,14803,14804,14822],[14761,14796,14807,20204,14754,14801,14806,14810,14766,14777,14803,42059,14740,14811,14815,14819,14748,14780,14782,14808,14739,14793,24493,24495,14743,14749,14774,14805,14741,14767,14783,40836,14746,14753,14769,14785,14784,14822,14827,16571,14751,14762,14776,24497,14759,14768,14781,14798,14775,14787,24500,42057,14756,14789,14797,14804,14773,14794,14814,14823,14747,14755,24491,14757,14758,14779,14826,42040,42041],[14743,14754,14787,14798,14752,14766,14770,14811,14806,14808,14813,17306,14782,14783,14814,24497,14755,14797,14803,14826,14756,14767,14769,14776,14739,14779,14794,43077,14823,24493,24500,24501,14775,14781,14796,14817,14746,14768,14773,14815,14760,14789,14793,30279,14757,14780,14810,39529,14740,14788,14819,20204,14747,14753,14761,14816,14741,14749,14807,14825,14744,14748,14820,14771,43076,43078,14745,14827,27824],[14758,14776,14804,14806,14798,14817,17949,20204,14797,14805,14807,24497,14773,14780,14796,14825,14742,14759,14803,14810,14784,14787,14811,39529,14746,14751,14789,44142,14743,14783,14819,14827,14770,14771,14782,27828,14745,14749,14816,42041,14753,14813,21031,24500,14744,14762,14815,14823,14755,14775,14778,14781,14769,14779,17306,17952,14740,14767,14774,16571,14747,14756,14801,14814,14785,17950,44143],[14756,14758,14794,14821,14742,14767,14769,26178,14748,14753,14773,14797,14754,14759,14766,14776,14757,14768,14796,14813,14746,14770,14793,14827,14755,14816,21031,39529,14777,14780,14787,20204,14752,14761,14781,45046,14744,14788,14817,24495,14741,14782,14810,24491,14739,14783,14785,14804,14743,14784,14806,14807,14774,14805,14808,27828,14740,14745,14778,24500,14749,14798,14803,14801,14825,45042,14789,14814,14819],[14752,14762,14794,39529,14745,14760,14815,24493,14773,14787,14789,14826,14739,14747,14766,14810,14748,14770,14779,14804,14744,20204,24500,27828,14756,14781,14806,14825,14749,14755,14784,14813,14776,14780,17952,24491,14743,14753,14754,14803,14742,14798,14808,14814,14783,14796,14819,26178,14746,14788,14827,17306,14761,14778,14797,24495,14741,14751,14775,14823,14767,14768,14820,16571,14740,14759,14801,14821,14769,14782,21031],[14741,14754,14771,14781,14740,14793,14796,14805,14745,14798,14823,20204,14755,14756,14768,14801,14739,14804,14815,24491,14774,14778,14784,14813,14744,14747,14773,14782,14746,14757,14821,14825,14751,14758,14780,14794,14770,14779,21031,24500,14767,14814,16574,26178,14748,14760,14803,14826,14742,14769,14788,14807,14761,14787,14808,39529,14759,14776,14810,43076,14749,14753,14766,14783,14775,14797,14806,14820,14827,26174,47065,14816,47066,47067],[14740,14778,14798,24497,14766,14823,21031,48399,14768,14779,14784,14810,14744,14781,14785,14816,14760,14761,14814,14826,14755,14758,14767,14782,14756,14771,14796,14820,14787,14803,14805,14815,14743,14749,14793,14801,14745,14757,14789,14825,14741,14748,14806,14827,14780,14808,14819,20204,14742,14751,14773,24500,14769,14807,24493,24495,14747,14770,14776,14821,14754,14804,14813],[14741,14755,14793,14821,14758,14768,14826,21031,14780,14796,14813,24493,14752,14798,14816,14825,14775,14776,14803,14808,14749,14814,24491,24500,14739,14742,14787,24495,14751,14756,14815,39529,14753,14757,14773,14778,14748,14781,14783,14805,14743,14762,14801,14810,14760,14766,14784,14807,14744,14761,14767,14797,14740,14746,14789,14806,14754,14759,14774,14819,14782,14788,14804,14827,14769,14770,20204,24497,14745,14794,14822]]");

        $players = [];
        foreach ($attendance as $round) {
            foreach ($round as $player) {
                $players[$player] = 'Player#'.($player);
            }
        }

        // Run this simulation 10 times
        for ($j=0;$j<10;$j++) {

            $previously_matched = [];
            $three_player_matches = [];
            // Simulate 5 rounds of play
            foreach ($attendance as $idx => $attending_players) {

                $current_players = [];
                foreach ($attending_players as $id) {
                    $current_players[$id] = $players[$id];
                }

                $player_count = count($current_players);

                $builder = new haugstrup\TournamentUtils\BalancedGreedyPairing($current_players, $previously_matched, 4, $three_player_matches);
                $result = $builder->build();

                $this->assertTrue(isset($result['cost']));
                $this->assertTrue(isset($result['groups']));
                $this->assertEquals(0, $result['cost']);

                // Add matchups to $previously_matched
                foreach ($result['groups'] as $group) {
                    foreach ($group as $player) {
                        $id = array_search($player, $players);
                        if (count($group) < 4) {
                            if (!isset($three_player_matches[$id])) {
                                $three_player_matches[$id] = 1;
                            } else {
                                $three_player_matches[$id]++;
                            }
                        }

                        foreach ($group as $c_player) {
                            if ($player !== $c_player) {
                                $c_id = array_search($c_player, $players);
                                $previously_matched[$id][] = $c_id;
                            }
                        }
                    }
                }
            }

            // All three player group counts must be `1` (no person has had any repeats)
            foreach ($three_player_matches as $count) {
                $this->assertEquals(1, $count);
            }

            // No player must have had any repeat opponents
            foreach ($previously_matched as $opponents) {
                $counts = array_count_values($opponents);
                $this->assertEquals(count($opponents), array_sum($counts));
            }

        }
    }

}
