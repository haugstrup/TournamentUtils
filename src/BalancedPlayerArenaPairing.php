<?php

namespace haugstrup\TournamentUtils;

require_once 'RandomOptimizer.php';

class BalancedPlayerArenaPairing extends RandomOptimizer
{
    public $group_size = 4;

    /** Array of player objects, keyed by the player id */
    public $players = [];

    /** Array of arena ids */
    public $arenas = [];

    public $previously_matched = [];

    public $three_player_group_counts = [];

    public $arena_plays = [];

    public $amount = 1;

    public function __construct(
        $players,
        $arenas,
        $previously_matched = [],
        $arena_plays = [],
        $group_size = 4,
        $three_player_group_counts = [],
        $amount = 1
    ) {
        $this->iterations = 100;
        $this->players = $players;
        $this->arenas = $arenas;
        $this->previously_matched = $previously_matched;
        $this->arena_plays = $arena_plays;
        $this->three_player_group_counts = $three_player_group_counts;
        $this->group_size = $group_size;
        $this->amount = $amount;

        if ($group_size !== 2 && $group_size !== 4) {
            throw new \Exception('Group size must be 2 or 4');
        }
    }

    public function solution($input)
    {
        $solution = [];

        // Shuffle players and arenas
        $ids = array_keys($input);
        shuffle($ids);
        $arenas = $this->arenas;
        shuffle($arenas);

        $player_count = count($ids);

        // Find number of three player groups needed
        $num_of_three_player_groups = 0;
        if ($this->group_size === 4) {
            $num_of_three_player_groups = 4 - ($player_count % 4);
            if ($num_of_three_player_groups === 4) {
                $num_of_three_player_groups = 0;
            }
        }

        // Generate array of empty groups with arena slots
        $solution = [];
        $num_groups = intval(ceil($player_count / $this->group_size));

        // Handle special case for smaller player counts with group_size = 4
        if ($this->group_size === 4) {
            if ($player_count <= 5) {
                $num_groups = 1;
            } elseif ($player_count <= 9) {
                $num_groups = 2;
            } elseif ($player_count <= 13) {
                $num_groups = 3;
            }
        }

        for ($i = 0; $i < $num_groups; $i++) {
            $group_size = $this->group_size;

            // Adjust for three-player groups at the end
            if ($this->group_size === 4 && $i >= ($num_groups - $num_of_three_player_groups)) {
                $group_size = 3;
            } elseif ($this->group_size === 2 && $player_count % 2 === 1 && $i === $num_groups - 1) {
                $group_size = 3;
            }

            $solution[] = [
                'players' => [],
                'arenas' => array_fill(0, $this->amount, null),
                'size' => $group_size,
                'cost' => 0,
            ];
        }

        // Create a list of available arena assignments for each round of $amount
        $arena_availability = [];
        for ($round = 0; $round < $this->amount; $round++) {
            $arena_availability[$round] = $arenas;
        }

        // Assign players to groups using greedy selection considering both pairing and arena costs
        foreach ($ids as $player_id) {
            $best_cost = null;
            $best_group_idx = null;
            $best_arena_assignment = null;

            // Try each group that still needs players
            foreach ($solution as $group_idx => $group) {
                if (count($group['players']) >= $group['size']) {
                    continue; // Group is full
                }

                // Calculate player pairing cost for this group
                $test_players = array_merge($group['players'], [$player_id]);
                $pairing_cost = $this->cost_for_players($test_players, $group['size'] === 3);

                // If this is the last player for the group, also consider arena assignment
                $arena_cost = 0;
                $arena_assignment = $group['arenas'];

                if (count($test_players) === $group['size']) {
                    // Group will be complete, find best arena assignment
                    $best_arena_cost = null;
                    $best_assignment = null;

                    // Try different arena combinations
                    for ($attempt = 0; $attempt < 10; $attempt++) { // Limit attempts for performance
                        $test_assignment = array_fill(0, $this->amount, null);
                        $test_cost = 0;

                        for ($round = 0; $round < $this->amount; $round++) {
                            if (empty($arena_availability[$round])) {
                                continue;
                            }

                            $available_in_round = $arena_availability[$round];
                            shuffle($available_in_round);

                            $round_best_cost = null;
                            $round_best_arena = null;

                            foreach ($available_in_round as $arena) {
                                $round_cost = $this->cost_for_arena_selection($test_players, $arena);
                                if (is_null($round_best_cost) || $round_cost < $round_best_cost) {
                                    $round_best_cost = $round_cost;
                                    $round_best_arena = $arena;
                                }
                            }

                            $test_assignment[$round] = $round_best_arena;
                            $test_cost += $round_best_cost;
                        }

                        if (is_null($best_arena_cost) || $test_cost < $best_arena_cost) {
                            $best_arena_cost = $test_cost;
                            $best_assignment = $test_assignment;
                        }
                    }

                    $arena_cost = $best_arena_cost ?? 0;
                    $arena_assignment = $best_assignment ?? $arena_assignment;
                }

                $total_cost = $pairing_cost + $arena_cost;

                if (is_null($best_cost) || $total_cost < $best_cost) {
                    $best_cost = $total_cost;
                    $best_group_idx = $group_idx;
                    $best_arena_assignment = $arena_assignment;
                }
            }

            // Assign player to best group
            if (! is_null($best_group_idx)) {
                $solution[$best_group_idx]['players'][] = $player_id;
                $solution[$best_group_idx]['cost'] = $best_cost;

                // If group is now complete, reserve the arenas
                if (count($solution[$best_group_idx]['players']) === $solution[$best_group_idx]['size']) {
                    $solution[$best_group_idx]['arenas'] = $best_arena_assignment;

                    // Remove assigned arenas from availability
                    for ($round = 0; $round < $this->amount; $round++) {
                        if ($best_arena_assignment[$round] !== null) {
                            $arena_availability[$round] = array_diff(
                                $arena_availability[$round],
                                [$best_arena_assignment[$round]]
                            );
                        }
                    }
                }
            }
        }

        // Convert to expected format
        $formatted_solution = [];
        foreach ($solution as $group) {
            $formatted_group = [
                'players' => [],
                'arenas' => $group['arenas'],
                'size' => $group['size'],
                'cost' => $group['cost'],
            ];

            foreach ($group['players'] as $player_id) {
                $formatted_group['players'][] = $this->players[$player_id];
            }

            $formatted_solution[] = $formatted_group;
        }

        return $formatted_solution;
    }

    public function cost_for_players($players, $three_player_group = false)
    {
        $cost = 0;

        // Three player group penalty
        if ($three_player_group) {
            foreach ($players as $id) {
                if (isset($this->three_player_group_counts[$id])) {
                    $cost += pow($this->three_player_group_counts[$id] * 2, 2);
                }
            }
        }

        // Repeated opponent penalties
        $opponent_counts = [];
        foreach ($players as $id) {
            if (isset($this->previously_matched[$id])) {
                foreach ($this->previously_matched[$id] as $opponent_id) {
                    if (in_array($opponent_id, $players)) {
                        if (! isset($opponent_counts[$id])) {
                            $opponent_counts[$id] = [];
                        }
                        if (! isset($opponent_counts[$id][$opponent_id])) {
                            $opponent_counts[$id][$opponent_id] = 0;
                        }
                        $opponent_counts[$id][$opponent_id]++;
                    }
                }
            }
        }

        // Primary repeated opponent cost (squared)
        foreach ($opponent_counts as $id => $opponents) {
            foreach ($opponents as $inner_id => $count) {
                $cost += pow($count, 2);
            }
        }

        // Additional fairness penalty
        foreach ($opponent_counts as $id => $opponents) {
            $repeat_opponent_count = 0;
            foreach ($opponents as $inner_id => $count) {
                if ($count > 0) {
                    $repeat_opponent_count++;
                }
            }
            if ($repeat_opponent_count > 1) {
                foreach ($opponents as $inner_id => $count) {
                    $cost += $count;
                }
            }
        }

        return $cost;
    }

    public function cost_for_arena_selection($players, $arena)
    {
        $cost = 0;
        $arena_counts = [];

        // Calculate how many times each player has played this arena
        foreach ($players as $player_id) {
            if (isset($this->arena_plays[$player_id][$arena])) {
                $cost += pow($this->arena_plays[$player_id][$arena], 2);
                $arena_counts[$player_id] = $this->arena_plays[$player_id][$arena];
            }
        }

        // Additional fairness penalty for multiple arena repeats
        foreach ($players as $player_id) {
            if (isset($arena_counts[$player_id]) && $arena_counts[$player_id] > 0) {
                if (isset($this->arena_plays[$player_id])) {
                    foreach ($this->arena_plays[$player_id] as $other_arena => $count) {
                        if ($other_arena !== $arena && $count > 0) {
                            $cost += $count;
                        }
                    }
                }
            }
        }

        return $cost;
    }

    public function cost($solution)
    {
        $cost = 0;
        foreach ($solution as $group) {
            $cost += $group['cost'];
        }

        return $cost;
    }

    public function build()
    {
        $result = $this->solve($this->players);

        return [
            'cost' => $result['cost'],
            'groups' => $result['solution'],
        ];
    }
}
