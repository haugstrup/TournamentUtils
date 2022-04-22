<?php
use PHPUnit\Framework\TestCase;
use haugstrup\TournamentUtils\HeadToHeadStrictSwissPairing;

class HeadToHeadStrictSwissPairingTest extends TestCase
{
    protected $debugData = [];

    public function setUp(): void
    {
        $this->debugData = [];
    }

    /** @test */
    public function it_produces_valid_data()
    {
        $playerCount = 21;
        $rounds = 50;
        for ($i = 0; $i < 1; ++$i) {
            $this->simulateTournament($playerCount, $rounds, null, [
        'onPairedRound' => function ($round, $beforePairingData, $pairings, $byes) {
            $playerCount = count(call_user_func_array('array_merge', $beforePairingData['groups']));
            // Right number of pairings
            $this->assertCount((int)floor($playerCount / 2), $pairings);
            // Each pairing contains exactly two players
            $uniquePairingLengths = array_unique(array_map('count', $pairings));
            $this->assertCount(1, $uniquePairingLengths);
            $this->assertEquals(2, $uniquePairingLengths[0]);
            // Right number of byes
            $this->assertCount($playerCount % 2, $byes);
            // No duplicate players across pairings and byes
            $pairedPlayers = call_user_func_array('array_merge', $pairings);
            $uniquePlayers = array_unique(array_merge($pairedPlayers, $byes));
            $this->assertCount($playerCount, $uniquePlayers);
        }
      ]);
        }
    }

    /** @test */
    public function it_awards_byes_to_players_with_the_least_amount_of_byes()
    {
        $playerCount = 21;
        $rounds = 50;
        for ($i = 0; $i < 1; ++$i) {
            $this->simulateTournament($playerCount, $rounds, null, [
        'onPlayedRound' => function ($round, $groups, $previous_opponents, $byes) use ($playerCount) {
            $maxByesAwardedToOnePlayer = $byes ? max($byes) : 0;
            // There should be no repeat byes until $playerCount + 1 rounds have been played
            // (so until the round index equals $playerCount)
            $maxExpectedByes = 1 + floor($round / $playerCount);
            $this->assertEquals($maxExpectedByes, $maxByesAwardedToOnePlayer);
        }
      ]);
        }
    }

    /** @test */
    public function it_doesnt_repeat_pairings_if_at_all_avoidable()
    {
        $playerCount = 20;
        // Practically, repeat pairings should be avoidable until almost all players have played
        // against each other, but just in case, test only for a large portion of the possible rounds.
        $rounds = floor($playerCount * .8);
        for ($i = 0; $i < 1; ++$i) {
            $this->simulateTournament($playerCount, $rounds, null, [
        'onFinishedTournament' => function ($groups, $previous_opponents, $byes) {
            foreach ($previous_opponents as $player => $opponentCounts) {
                $this->assertEquals(1, max($opponentCounts));
            }
        }
      ]);
        }
    }

    /** @test */
    public function it_prefers_pairings_between_players_of_similar_rank()
    {
        $playerCount = 50;
        // This test doesn't check whether similar rank pairings are actually possible, so only run it
        // for the length of a classic Swiss tournament where those pairings are sure to exist
        $rounds = ceil(log($playerCount, 2));
        for ($i = 0; $i < 1; ++$i) {
            $this->simulateTournament($playerCount, $rounds, null, [
        'onPairedRound' => function ($roundIndex, $beforePairingData, $pairings, $byes) use ($playerCount) {
            $groups = $this->getGroupsForMatchmaker($beforePairingData['groups']);
            $scores = $this->getScores($groups);
            $floaters = [];
            foreach ($pairings as $pairing) {
                $scoreDifference = abs($scores[$pairing[0]] - $scores[$pairing[1]]);
                if ($scoreDifference > 0) {
                    $floaters [] = $pairing;
                }
                $this->debugData['general'] = [
              'round' => $roundIndex,
              'pairing' => $pairing,
              'scoreDifference' => $scoreDifference,
            ];
                // Nobody should have floated down more than one group
                $this->assertLessThanOrEqual(1, $scoreDifference);
            }
            // Each group can have a floater if it is of odd size, but there shouldn't be more than that
            $this->assertLessThan(count($groups), count($floaters));
        }
      ]);
        }
    }

    /** @test */
    public function it_prefers_to_give_mismatched_pairings_to_lower_ranked_players()
    {
        $groups = [
      ['P1'],
      ['P2A', 'P2B'],
      ['P3A', 'P3B'],
      ['P4'],
    ];
        // There are two symmetric pairing options (the number next to the player is the number
        // of groups they have to float, i.e. it indicates how mismatched a pairing they got):
        // Option A: P1: 1, P2A: 1, P2B: 2, P3B: 0, P3A: 0, P4: 2
        // Option B: P1: 2, P2A: 0, P2B: 0, P3B: 2, P3A: 1, P4: 1
        $previous_opponents = [
      'P1' => ['P3A' => 1, 'P2B' => 1, 'P4' => 1],
      'P2A' => ['P3A' => 1, 'P3B' => 1, 'P4' => 1],
      'P2B' => ['P1' => 1, 'P3A' => 1, 'P3B' => 1],
      'P3A' => ['P1' => 1, 'P2A' => 1, 'P2B' => 1],
      'P3B' => ['P2A' => 1, 'P2B' => 1, 'P4' => 1],
      'P4' => ['P1' => 1, 'P2A' => 1, 'P3B' => 1],
    ];
        $pairings = $this->getPairingData($groups, $previous_opponents)['groups'];
        foreach ($pairings as &$pairing) {
            sort($pairing);
        }
        // Option A is preferable, because it gives ranks 1 and 3 better (less mismatched) pairings,
        // whereas option B would give ranks 2 and 4 better pairings
        $this->assertContains(['P1', 'P2A'], $pairings);
    }

    /** @test */
    public function it_prefers_to_award_byes_to_lower_ranked_players()
    {
        $playerCount = 21;
        // For a number of rounds very near $playerCount, avoiding repeat pairings
        // might override bye considerations, so don't set $rounds too high
        $rounds = floor($playerCount / 2);
        // Only the bottom 1/4 of bye eligible players should actually ever receive a bye
        $bottomPercentAllowedToGetBye = .25;
        for ($i = 0; $i < 1; ++$i) {
            $this->simulateTournament($playerCount, $rounds, null, [
        'onPairedRound' => function ($round, $beforePairingData, $pairings, $byes) use ($bottomPercentAllowedToGetBye) {
            $groups = $this->getGroupsForMatchmaker($beforePairingData['groups']);
            $previousByes = $beforePairingData['byes'];
            $players = call_user_func_array('array_merge', $groups);
            $playersWithBye = array_keys(array_filter($previousByes, function ($byeCount) {
                return $byeCount > 0;
            }));
            $eligibleByePlayers = array_values(array_diff($players, $playersWithBye));
            $acceptableByePlayersCount = ceil(count($eligibleByePlayers) * $bottomPercentAllowedToGetBye);
            $acceptableByePlayers = [];
            $groupsInByeEligibilityOrder = array_reverse($groups);
            // Go through all the groups bottom to top and add eligible players until
            // the specified percentage of acceptable bye players is reached
            foreach ($groupsInByeEligibilityOrder as $group) {
                if (count($acceptableByePlayers) >= $acceptableByePlayersCount) {
                    break;
                }
                // Add all players from this score group
                $acceptableByePlayers = array_merge($acceptableByePlayers, $group);
                // Remove players who already had a bye
                $acceptableByePlayers = array_values(array_intersect(
                    $eligibleByePlayers,
                    $acceptableByePlayers
                ));
            }
            $this->debugData['general'] = [
            'round' => $round,
            'byeAwardedTo' => $byes[0],
            'acceptableByePlayers' => $acceptableByePlayers,
            'eligibleByePlayers' => $eligibleByePlayers,
          ];
            $this->assertContains($byes[0], $acceptableByePlayers);
        }
      ]);
        }
    }

    /** @test */
    public function it_prefers_to_assign_repeat_pairings_to_lower_ranked_players()
    {
        $groups = [
      ['P1'],
      ['P2', 'P4'],
      ['P3', 'P5'],
      ['P6'],
    ];
        // The pairable players are two disjunct groups of odd size, so perfect matching is impossible
        $previous_opponents = [
      'P1' => ['P4' => 1, 'P5' => 1, 'P6' => 1],
      'P2' => ['P4' => 1, 'P5' => 1, 'P6' => 1],
      'P3' => ['P4' => 1, 'P5' => 1, 'P6' => 1],
      'P4' => ['P1' => 1, 'P2' => 1, 'P3' => 1],
      'P5' => ['P1' => 1, 'P2' => 1, 'P3' => 1],
      'P6' => ['P1' => 1, 'P2' => 1, 'P3' => 1],
    ];

        $pairings = $this->getPairingData($groups, $previous_opponents)['groups'];
        foreach ($pairings as &$pairing) {
            sort($pairing);
        }
        // The one unavoidable repeat pairing should be assigned to the lowest ranked players
        $this->assertContains(['P3', 'P6'], $pairings);
        // The best possible pairing should be applied to the highest ranked players
        $this->assertContains(['P1', 'P2'], $pairings);
        // The remaining pairing
        $this->assertContains(['P4', 'P5'], $pairings);
    }

    /** @test */
    public function it_balances_repeat_pairings_in_knockout_tournaments()
    {
        $playerCount = 50;
        $strikes = 5;
        for ($i = 0; $i < 1; ++$i) {
            $this->simulateTournament($playerCount, PHP_INT_MAX, $strikes, [
        'onPairedRound' => function ($round, $beforePairingData, $pairings, $byes) {
            $pairedPlayers = call_user_func_array('array_merge', $pairings);
            $pairingsInBothDirections = array_merge($pairings, array_map('array_reverse', $pairings));
            $pairingsLookupKeys = array_map(function ($pairing) {
                return $pairing[0];
            }, $pairingsInBothDirections);
            $pairingsLookupValues = array_map(function ($pairing) {
                return $pairing[1];
            }, $pairingsInBothDirections);
            $pairingsLookup = array_combine($pairingsLookupKeys, $pairingsLookupValues);

            foreach ($pairedPlayers as $player) {
                $possibleOpponents = array_diff($pairedPlayers, [$player]);
                $pairingCounts = $beforePairingData['previous_opponents'][$player] ?? [];
                // Make every possible opponent show up in the lookup, even the ones not previously met
                $pairingCountsLookup = array_merge(array_fill_keys($possibleOpponents, 0), $pairingCounts);
                // Remove eliminated players ($pairingsLookup contains only the keys of still active players)
                $pairingCountsLookup = array_intersect_key($pairingCountsLookup, $pairingsLookup);
                $opponent = $pairingsLookup[$player];
                $this->debugData['general'] = compact('round', 'player', 'opponent');
                $this->debugData['general']['previouslyPairedTimes'] = $pairingCountsLookup[$opponent];
                $this->debugData['general']['otherOpponentsPairedTimes'] = $pairingCountsLookup;
                $edge = array_values(array_filter(
                    $this->debugData['matchmaker']['edges'],
                    function ($edge) use ($player, $opponent) {
                  return (in_array($player, $edge) && in_array($opponent, $edge));
              }
                ))[0];
                $this->debugData['general']['edgeWeight'] = $edge[2];
                // When players start dropping out, it might not be possible to keep repeat pairings
                // completely balanced across players, so allow for a player to be paired with an opponent
                // whose repeat pairing counter is higher by one than the lowest repeat pairing counter.
                $allowedDifferenceInPairingRepeatCount = 1;
                $this->assertLessThanOrEqual(
                    min($pairingCountsLookup) + $allowedDifferenceInPairingRepeatCount,
                    $pairingCountsLookup[$opponent]
                );
            }
        }
      ]);
        }
    }

    /** @test */
    public function it_converts_groups_to_pseudoscores()
    {
        $groups = [
      ['P1'],
      ['P2', 'P3'],
      [],
      ['P4'],
      [],
    ];
        $matchmaker = new HeadToHeadStrictSwissPairing($groups, []);
        $this->assertEquals(['P1' => 4, 'P2' => 3, 'P3' => 3, 'P4' => 1], $matchmaker->getPseudoscores());
    }

    // If $strikes is null, this simulates a points tournament where winners ascend one group.
    // If $strikes is n > 0, this simulates a strikes tournament where losers descend one group or get eliminated
    protected function simulateTournament($playerCount, $rounds, $strikes = null, $callbacks = [])
    {
        $data = $this->getFreshTournament($playerCount);
        for ($round = 0; $round < $rounds; ++$round) {
            $data = $this->simulateRound($data, $round, $callbacks);
            $data['groups'] = $this->removeDeadPlayers($data['groups'], $round, $strikes);
            $this->trigger('onPlayedRound', $callbacks, $round, ...array_values($data));
            // If only one player remains in a knockout tournament, it's over
            if ($strikes && count(call_user_func_array('array_merge', $data['groups'])) === 1) {
                break;
            }
        }
        $this->trigger('onFinishedTournament', $callbacks, ...array_values($data));
    }

    protected function simulateRound($data, $round, $callbacks = [])
    {
        list($groups, $previous_opponents, $byes) = array_values($data);
        $matchmakerResults = $this->getPairingData($this->getGroupsForMatchmaker($groups), $previous_opponents, $byes);
        $this->trigger('onPairedRound', $callbacks, $round, $data, ...array_values($matchmakerResults));
        $newPairings = $matchmakerResults['groups'];
        $newByes = $matchmakerResults['byes'];
        // Set previous opponents
        foreach ($newPairings as $pairing) {
            $previous_opponents[$pairing[0]][$pairing[1]] = ($previous_opponents[$pairing[0]][$pairing[1]] ?? 0) + 1;
            $previous_opponents[$pairing[1]][$pairing[0]] = ($previous_opponents[$pairing[1]][$pairing[0]] ?? 0) + 1;
        }
        // Set byes
        foreach ($newByes as $byePlayer) {
            $byes[$byePlayer] = ($byes[$byePlayer] ?? 0) + 1;
        }
        // Set groups
        $groups = $this->assignRandomWins($groups, $matchmakerResults);
        return compact('groups', 'previous_opponents', 'byes');
    }

    protected function removeDeadPlayers($groups, $afterRoundIndex, $initialLives)
    {
        if (! $initialLives) {
            return $groups;
        }
        $playedRounds = $afterRoundIndex + 1;
        $playerIsDeadAtScore = $playedRounds - $initialLives;
        $ascendingScoreGroups = array_reverse($groups);
        foreach ($ascendingScoreGroups as $i => &$group) {
            if ($i <= $playerIsDeadAtScore) {
                $group = [];
            }
        }
        return array_reverse($ascendingScoreGroups);
    }

    protected function getFreshTournament($playerCount)
    {
        $groups = [[]];
        $previous_opponents = [];
        $byes = [];
        for ($i = 0; $i < $playerCount; ++$i) {
            $playerName = 'Player ' . ($i + 1);
            $groups[0] []= $playerName;
        }
        return compact('groups', 'previous_opponents', 'byes');
    }

    protected function getScores($groups)
    {
        return (new HeadToHeadStrictSwissPairing($groups, []))->getPseudoscores();
    }

    protected function assignRandomWins($oldGroups, $matchmakerResults)
    {
        $scores = $this->getScores($oldGroups);
        foreach ($matchmakerResults['byes'] as $byePlayer) {
            $scores[$byePlayer] += 1;
        }
        foreach ($matchmakerResults['groups'] as $pairing) {
            // Higher ranked player is more likely to win
            $playerOneScore = $scores[$pairing[0]];
            $playerTwoScore = $scores[$pairing[1]];
            $totalScore = $playerOneScore + $playerTwoScore;
            $playerOneWinBias = $totalScore ? $playerOneScore / $totalScore : .5;
            $winner = lcg_value() < $playerOneWinBias ? $pairing[0] : $pairing[1];
            $scores[$winner] += 1;
        }
        $newGroups = [];
        $maxScore = max($scores);
        for ($score = 0; $score < $maxScore; ++$score) {
            $newGroups[$score] = [];
        }
        foreach ($scores as $player => $score) {
            $newGroups[$score] []= $player;
        }
        return array_reverse($newGroups);
    }

    // In order to keep scores across the tournament simulation, the $groups array maintained by the
    // simulator consists of an array for each score level, even if no player has that score.
    // Remove those empty arrays for matchmaking, since they won't be available in production.
    protected function getGroupsForMatchmaker($groups)
    {
        return array_values(array_filter($groups, function ($group) {
            return $group;
        }));
    }

    protected function getPairingData($groups, $previous_opponents, $byes = [])
    {
        $this->debugData['matchmaker']['input'] = compact('groups', 'previous_opponents', 'byes');
        $matchmaker = new HeadToHeadStrictSwissPairing($groups, $previous_opponents, $byes);
        $matchmakerResult = $matchmaker->build();
        $this->debugData['matchmaker']['edges'] = $matchmaker->getDebugEdges();
        $this->debugData['matchmaker']['output'] = $matchmakerResult;
        return $matchmakerResult;
    }

    protected function trigger($event, $callbacks, ...$params)
    {
        if (! is_callable($callbacks[$event] ?? null)) {
            return;
        }
        $callbacks[$event](...$params);
    }

    public function printDebugInfo()
    {
        if (! $this->debugData) {
            return printf("The test recorded no debug data.");
        }
        printf("\n\n");
        if (isset($this->debugData['general'])) {
            printf("The following general debug data was recorded:\n\n");
            printf(json_encode($this->debugData['general'], JSON_PRETTY_PRINT) . "\n\n\n");
        }
        printf("Matchmaker was called with the following input:\n\n");
        printf(json_encode($this->debugData['matchmaker']['input'], JSON_PRETTY_PRINT) . "\n\n\n");

        printf("Use these definitions to create a specific test case if necessary:\n\n");
        $definitions = "\$groups = json_decode('%s', true);\n" .
      "\$previous_opponents = json_decode('%s', true);\n" .
      "\$byes = json_decode('%s', true);\n\n\n";
        call_user_func_array('printf', array_merge(
            [$definitions],
            array_map('json_encode', $this->debugData['matchmaker']['input'])
        ));

        printf("The resulting pairing data was:\n\n");
        printf(json_encode($this->debugData['matchmaker']['output'], JSON_PRETTY_PRINT) . "\n\n\n");

        printf("Matchmaker used the following edge weights to calculate pairings:\n\n");
        printf(json_encode($this->debugData['matchmaker']['edges'], JSON_PRETTY_PRINT) . "\n\n\n");
        printf("\n\n");
    }
}
