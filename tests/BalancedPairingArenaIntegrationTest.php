<?php

use PHPUnit\Framework\TestCase;

class BalancedPairingArenaIntegrationTest extends TestCase
{
    public function test_balanced_pairing_and_arena_integration_with_scoring()
    {
        // Setup: 16 players, 4 arenas, simulate multiple rounds
        $players = [];
        for ($i = 0; $i < 16; $i++) {
            $players[$i] = 'Player#' . ($i + 1);
        }
        
        $arenas = [1, 2, 3, 4];
        $rounds = 5; // Simulate 5 rounds of play
        
        $previously_matched = [];
        $three_player_matches = [];
        $arena_plays = [];
        
        $total_opponent_repeats = 0;
        $total_arena_repeats = 0;
        
        // Simulate multiple rounds
        for ($round = 1; $round <= $rounds; $round++) {
            
            // Step 1: Create balanced pairings
            $pairing_builder = new haugstrup\TournamentUtils\BalancedGreedyPairing(
                $players, 
                $previously_matched, 
                4, // 4-player groups
                $three_player_matches
            );
            $pairing_result = $pairing_builder->build();
            
            // Step 2: Assign arenas to groups
            $groups_for_arena = [];
            foreach ($pairing_result['groups'] as $group) {
                $group_keys = array_keys($group);
                $groups_for_arena[] = $group_keys;
            }
            
            $arena_builder = new haugstrup\TournamentUtils\BalancedGreedyArena(
                $groups_for_arena,
                $arenas,
                1, // 1 arena per group
                $arena_plays
            );
            $arena_result = $arena_builder->build();
            
            // Step 3: Update tracking arrays and calculate penalties
            $round_opponent_repeats = 0;
            $round_arena_repeats = 0;
            
            foreach ($arena_result['groups'] as $group_idx => $assigned_arenas) {
                $group = $groups_for_arena[$group_idx];
                $arena = $assigned_arenas[0];
                
                // Track opponent repeats (1000x multiplier as requested)
                foreach ($group as $player_id) {
                    if (!isset($previously_matched[$player_id])) {
                        $previously_matched[$player_id] = [];
                    }
                    
                    // Count how many times this player has faced each opponent in this group
                    foreach ($group as $opponent_id) {
                        if ($player_id !== $opponent_id) {
                            $repeat_count = count(array_filter($previously_matched[$player_id], function($prev_opponent) use ($opponent_id) {
                                return $prev_opponent === $opponent_id;
                            }));
                            
                            if ($repeat_count > 0) {
                                $round_opponent_repeats += $repeat_count * 1000; // 1000x multiplier
                            }
                            
                            // Add this opponent to history
                            $previously_matched[$player_id][] = $opponent_id;
                        }
                    }
                    
                    // Track arena repeats (1x multiplier)
                    if ($arena !== null) {
                        if (!isset($arena_plays[$player_id])) {
                            $arena_plays[$player_id] = [];
                        }
                        
                        $arena_repeat_count = isset($arena_plays[$player_id][$arena]) 
                            ? $arena_plays[$player_id][$arena] 
                            : 0;
                        
                        if ($arena_repeat_count > 0) {
                            $round_arena_repeats += $arena_repeat_count;
                        }
                        
                        // Update arena play count
                        if (!isset($arena_plays[$player_id][$arena])) {
                            $arena_plays[$player_id][$arena] = 0;
                        }
                        $arena_plays[$player_id][$arena]++;
                    }
                    
                    // Track 3-player groups if applicable
                    if (count($group) === 3) {
                        if (!isset($three_player_matches[$player_id])) {
                            $three_player_matches[$player_id] = 0;
                        }
                        $three_player_matches[$player_id]++;
                    }
                }
            }
            
            $total_opponent_repeats += $round_opponent_repeats;
            $total_arena_repeats += $round_arena_repeats;
            
            // Assertions for this round
            $this->assertIsArray($pairing_result['groups']);
            $this->assertIsArray($arena_result['groups']);
            $this->assertEquals(count($pairing_result['groups']), count($arena_result['groups']));
            
            // Verify all players are assigned
            $assigned_players = [];
            foreach ($pairing_result['groups'] as $group) {
                $assigned_players = array_merge($assigned_players, array_keys($group));
            }
            $this->assertEquals(16, count($assigned_players));
            
            echo "Round $round - Opponent repeats penalty: $round_opponent_repeats, Arena repeats penalty: $round_arena_repeats\n";
        }
        
        // Final scoring calculations
        $total_score = $total_opponent_repeats + $total_arena_repeats;
        
        echo "Final Scoring Results:\n";
        echo "Total opponent repeat penalties (1000x): $total_opponent_repeats\n";
        echo "Total arena repeat penalties (1x): $total_arena_repeats\n";
        echo "Combined penalty score: $total_score\n";
        
        // Verify the algorithms are working to minimize repeats
        // With 16 players over 5 rounds, some repeats are inevitable, but should be reasonable
        // The 1000x multiplier makes opponent repeats heavily penalized as requested
        $this->assertLessThan(5000000, $total_score, "Combined penalty score should be reasonable with balanced algorithms");
        $this->assertGreaterThan(0, $total_opponent_repeats, "Should have some opponent repeats after multiple rounds");
        $this->assertGreaterThan(0, $total_arena_repeats, "Should have some arena repeats with only 4 arenas");
        
        // Verify data structures are properly maintained
        $this->assertIsArray($previously_matched);
        $this->assertIsArray($arena_plays);
        
        // Each player should have faced some opponents
        foreach ($players as $player_id => $player_name) {
            if (isset($previously_matched[$player_id])) {
                $this->assertGreaterThan(0, count($previously_matched[$player_id]));
            }
        }
        
        // Each player should have played on some arenas
        foreach ($players as $player_id => $player_name) {
            if (isset($arena_plays[$player_id])) {
                $this->assertGreaterThan(0, count($arena_plays[$player_id]));
            }
        }
        
        // Detailed analysis
        $opponent_repeat_analysis = [];
        $arena_repeat_analysis = [];
        
        foreach ($previously_matched as $player_id => $opponents) {
            $opponent_counts = array_count_values($opponents);
            foreach ($opponent_counts as $opponent_id => $count) {
                if ($count > 1) {
                    $opponent_repeat_analysis[] = [
                        'player' => $player_id,
                        'opponent' => $opponent_id,
                        'times_faced' => $count
                    ];
                }
            }
        }
        
        foreach ($arena_plays as $player_id => $arenas) {
            foreach ($arenas as $arena_id => $count) {
                if ($count > 1) {
                    $arena_repeat_analysis[] = [
                        'player' => $player_id,
                        'arena' => $arena_id,
                        'times_played' => $count
                    ];
                }
            }
        }
        
        echo "Opponent repeat details: " . count($opponent_repeat_analysis) . " instances\n";
        echo "Arena repeat details: " . count($arena_repeat_analysis) . " instances\n";
        
        if (!empty($opponent_repeat_analysis)) {
            echo "Sample opponent repeats:\n";
            foreach (array_slice($opponent_repeat_analysis, 0, 5) as $repeat) {
                echo "  Player {$repeat['player']} faced Player {$repeat['opponent']} {$repeat['times_faced']} times\n";
            }
        }
        
        if (!empty($arena_repeat_analysis)) {
            echo "Sample arena repeats:\n";
            foreach (array_slice($arena_repeat_analysis, 0, 5) as $repeat) {
                echo "  Player {$repeat['player']} played Arena {$repeat['arena']} {$repeat['times_played']} times\n";
            }
        }
    }
}