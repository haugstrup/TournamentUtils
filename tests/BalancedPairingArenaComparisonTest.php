<?php

use PHPUnit\Framework\TestCase;

class BalancedPairingArenaComparisonTest extends TestCase
{
    public function test_separate_vs_combined_approach_scoring_comparison()
    {
        // Setup: 16 players, 4 arenas, simulate multiple rounds
        $players = [];
        for ($i = 0; $i < 16; $i++) {
            $players[$i] = 'Player#' . ($i + 1);
        }
        
        $arenas = [1, 2, 3, 4];
        $rounds = 5; // Simulate 5 rounds of play
        
        echo "\n=== COMPARISON: Separate vs Combined Pairing+Arena Assignment ===\n\n";
        
        // Test both approaches
        $results = [
            'separate' => $this->simulate_separate_approach($players, $arenas, $rounds),
            'combined' => $this->simulate_combined_approach($players, $arenas, $rounds)
        ];
        
        // Compare results
        foreach (['separate', 'combined'] as $approach) {
            $result = $results[$approach];
            echo strtoupper($approach) . " APPROACH RESULTS:\n";
            echo "Total opponent repeat penalties (1000x): {$result['opponent_penalties']}\n";
            echo "Total arena repeat penalties (1x): {$result['arena_penalties']}\n";
            echo "Combined penalty score: {$result['total_score']}\n";
            echo "Unique opponent repeats: {$result['opponent_repeat_instances']}\n";
            echo "Unique arena repeats: {$result['arena_repeat_instances']}\n\n";
        }
        
        // Verify both approaches work
        $this->assertGreaterThan(0, $results['separate']['total_score']);
        $this->assertGreaterThan(0, $results['combined']['total_score']);
        
        // The combined approach should have fewer arena repeat penalties
        $separate_arena_penalties = $results['separate']['arena_penalties'];
        $combined_arena_penalties = $results['combined']['arena_penalties'];
        
        echo "IMPROVEMENT ANALYSIS:\n";
        echo "Arena penalty reduction: " . ($separate_arena_penalties - $combined_arena_penalties) . "\n";
        echo "Arena penalty improvement: " . round((($separate_arena_penalties - $combined_arena_penalties) / $separate_arena_penalties) * 100, 2) . "%\n";
        
        $separate_total = $results['separate']['total_score'];
        $combined_total = $results['combined']['total_score'];
        echo "Total score reduction: " . ($separate_total - $combined_total) . "\n";
        echo "Total score improvement: " . round((($separate_total - $combined_total) / $separate_total) * 100, 2) . "%\n\n";
        
        // The combined approach should generally perform better or equal for arena assignments
        $this->assertLessThanOrEqual(
            $separate_arena_penalties, 
            $combined_arena_penalties,
            "Combined approach should have fewer or equal arena repeat penalties"
        );
        
        // Overall score should be better or equal
        $this->assertLessThanOrEqual(
            $separate_total,
            $combined_total,
            "Combined approach should have better or equal total score"
        );
        
        // Detailed analysis
        echo "DETAILED COMPARISON:\n";
        foreach (['separate', 'combined'] as $approach) {
            $result = $results[$approach];
            echo "$approach approach:\n";
            echo "  - Average arena repeats per instance: " . 
                 round($result['arena_penalties'] / max(1, $result['arena_repeat_instances']), 2) . "\n";
            echo "  - Average opponent repeats per instance: " . 
                 round($result['opponent_penalties'] / max(1, $result['opponent_repeat_instances']) / 1000, 2) . "\n";
        }
    }

    private function simulate_separate_approach($players, $arenas, $rounds)
    {
        $previously_matched = [];
        $three_player_matches = [];
        $arena_plays = [];
        
        $total_opponent_penalties = 0;
        $total_arena_penalties = 0;
        $opponent_repeat_instances = 0;
        $arena_repeat_instances = 0;
        
        for ($round = 1; $round <= $rounds; $round++) {
            // Step 1: Create balanced pairings (separate)
            $pairing_builder = new haugstrup\TournamentUtils\BalancedGreedyPairing(
                $players, 
                $previously_matched, 
                4,
                $three_player_matches
            );
            $pairing_result = $pairing_builder->build();
            
            // Step 2: Assign arenas to groups (separate)
            $groups_for_arena = [];
            foreach ($pairing_result['groups'] as $group) {
                $group_keys = array_keys($group);
                $groups_for_arena[] = $group_keys;
            }
            
            $arena_builder = new haugstrup\TournamentUtils\BalancedGreedyArena(
                $groups_for_arena,
                $arenas,
                1,
                $arena_plays
            );
            $arena_result = $arena_builder->build();
            
            // Calculate penalties and update tracking
            $round_results = $this->calculate_penalties_and_update_tracking(
                $arena_result, $groups_for_arena, $previously_matched, $arena_plays, $three_player_matches
            );
            
            $total_opponent_penalties += $round_results['opponent_penalties'];
            $total_arena_penalties += $round_results['arena_penalties'];
            $opponent_repeat_instances += $round_results['opponent_instances'];
            $arena_repeat_instances += $round_results['arena_instances'];
        }
        
        return [
            'opponent_penalties' => $total_opponent_penalties,
            'arena_penalties' => $total_arena_penalties,
            'total_score' => $total_opponent_penalties + $total_arena_penalties,
            'opponent_repeat_instances' => $opponent_repeat_instances,
            'arena_repeat_instances' => $arena_repeat_instances
        ];
    }

    private function simulate_combined_approach($players, $arenas, $rounds)
    {
        $previously_matched = [];
        $three_player_matches = [];
        $arena_plays = [];
        
        $total_opponent_penalties = 0;
        $total_arena_penalties = 0;
        $opponent_repeat_instances = 0;
        $arena_repeat_instances = 0;
        
        for ($round = 1; $round <= $rounds; $round++) {
            // Combined pairing and arena assignment
            $combined_builder = new haugstrup\TournamentUtils\BalancedGreedyPairingArena(
                $players,
                $arenas,
                $previously_matched,
                $arena_plays,
                4,
                $three_player_matches,
                1
            );
            $combined_result = $combined_builder->build();
            
            // Convert to format expected by penalty calculation
            $groups_for_arena = [];
            $arena_assignments = [];
            foreach ($combined_result['groups'] as $idx => $group) {
                $group_keys = [];
                foreach ($group['players'] as $player_name) {
                    $player_id = array_search($player_name, $players);
                    $group_keys[] = $player_id;
                }
                $groups_for_arena[] = $group_keys;
                $arena_assignments[] = $group['arenas'];
            }
            
            $formatted_arena_result = [
                'groups' => $arena_assignments
            ];
            
            // Calculate penalties and update tracking
            $round_results = $this->calculate_penalties_and_update_tracking(
                $formatted_arena_result, $groups_for_arena, $previously_matched, $arena_plays, $three_player_matches
            );
            
            $total_opponent_penalties += $round_results['opponent_penalties'];
            $total_arena_penalties += $round_results['arena_penalties'];
            $opponent_repeat_instances += $round_results['opponent_instances'];
            $arena_repeat_instances += $round_results['arena_instances'];
        }
        
        return [
            'opponent_penalties' => $total_opponent_penalties,
            'arena_penalties' => $total_arena_penalties,
            'total_score' => $total_opponent_penalties + $total_arena_penalties,
            'opponent_repeat_instances' => $opponent_repeat_instances,
            'arena_repeat_instances' => $arena_repeat_instances
        ];
    }

    private function calculate_penalties_and_update_tracking($arena_result, $groups_for_arena, &$previously_matched, &$arena_plays, &$three_player_matches)
    {
        $round_opponent_penalties = 0;
        $round_arena_penalties = 0;
        $opponent_instances = 0;
        $arena_instances = 0;
        
        foreach ($arena_result['groups'] as $group_idx => $assigned_arenas) {
            $group = $groups_for_arena[$group_idx];
            $arena = $assigned_arenas[0];
            
            // Track opponent repeats BEFORE updating history
            foreach ($group as $player_id) {
                if (!isset($previously_matched[$player_id])) {
                    $previously_matched[$player_id] = [];
                }
                
                foreach ($group as $opponent_id) {
                    if ($player_id !== $opponent_id) {
                        $repeat_count = count(array_filter($previously_matched[$player_id], function($prev_opponent) use ($opponent_id) {
                            return $prev_opponent === $opponent_id;
                        }));
                        
                        if ($repeat_count > 0) {
                            $round_opponent_penalties += $repeat_count * 1000;
                            $opponent_instances++;
                        }
                    }
                }
            }
            
            // Track arena repeats BEFORE updating counts
            foreach ($group as $player_id) {
                if ($arena !== null) {
                    if (!isset($arena_plays[$player_id])) {
                        $arena_plays[$player_id] = [];
                    }
                    
                    $arena_repeat_count = isset($arena_plays[$player_id][$arena]) 
                        ? $arena_plays[$player_id][$arena] 
                        : 0;
                    
                    if ($arena_repeat_count > 0) {
                        $round_arena_penalties += $arena_repeat_count;
                        $arena_instances++;
                    }
                }
            }
            
            // NOW update the tracking arrays
            foreach ($group as $player_id) {
                // Add opponents to history
                foreach ($group as $opponent_id) {
                    if ($player_id !== $opponent_id) {
                        $previously_matched[$player_id][] = $opponent_id;
                    }
                }
                
                // Update arena play count
                if ($arena !== null) {
                    if (!isset($arena_plays[$player_id][$arena])) {
                        $arena_plays[$player_id][$arena] = 0;
                    }
                    $arena_plays[$player_id][$arena]++;
                }
                
                // Track 3-player groups
                if (count($group) === 3) {
                    if (!isset($three_player_matches[$player_id])) {
                        $three_player_matches[$player_id] = 0;
                    }
                    $three_player_matches[$player_id]++;
                }
            }
        }
        
        return [
            'opponent_penalties' => $round_opponent_penalties,
            'arena_penalties' => $round_arena_penalties,
            'opponent_instances' => $opponent_instances,
            'arena_instances' => $arena_instances
        ];
    }
}