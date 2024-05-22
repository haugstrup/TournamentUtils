<?php

namespace haugstrup\TournamentUtils;

require_once 'RandomOptimizer.php';

class BalancedGreedyArena extends RandomOptimizer
{
    // The groups to assign arenas to. Each group is an array
    // of player ids.
    public $groups = [];

    // Array of arena ids that can be assigned.
    public $available_arenas = [];

    // Number of times each player has played each arena previously.
    // Used in the cost function to avoid assigning the same machine again.
    public $arena_plays = [];

    // The number of arenas to assign each group
    public $amount = 1;

    // Array of strings. Represents groups/matches to never
    // assign an arena. Each entry in the array looks like 'x,y'
    // where x is the group index and y is the game index for that group
    public $skip_list = [];

    public function __construct(
        $groups,
        $available_arenas,
        $amount = 1,
        $arena_plays = []
    ) {
        $this->iterations = 100;
        $this->groups = $groups;
        $this->available_arenas = $available_arenas;
        $this->arena_plays = $arena_plays;
        $this->amount = $amount;
    }

    public function solution($input)
    {
        // We need to pick $mount number of arenas for each group.
        // Each arena can only be assigned once in each iteration of $amount
        $solution = [];

        // $group_keys = array_keys($input['groups']);
        // $group_keys = $this->shuffle($group_keys);
        // $input['available_arenas'] = $this->shuffle($input['available_arenas']);

        for($i = 0; $i < $this->amount; $i++) {
            // Shuffle the list of arenas and groups to get a fresh
            // starting point for each iteration
            $arenas = $this->shuffle($input['available_arenas']);
            $group_keys = $this->shuffle(array_keys($input['groups']));

            // For each group find the lowest cost arena
            $subsolution = [
                'arenas' => [],
                'cost' => 0,
            ];
            foreach ($group_keys as $group_index) {
                // If there are no more arenas available, bail out early
                if (count($arenas) < 1) {
                    continue;
                }

                // If this entry is in the skip list, skip it
                if (in_array($group_index.','.$i, $this->skip_list)) {
                    continue;
                }

                // Go through each of the available arenas and find the one
                // with the lowest cost. This is the "greedy" optimization
                // where we search through the entire list of arenas each
                // iteration to find the best matchup for this particular
                // group of people.
                $best_cost = 0;
                $best_cost_delta = 0;
                $best_arena = null;
                $best_arena_index = null;
                foreach ($arenas as $arena_index => $arena) {
                    $current_cost = $this->cost_for_selection($input['groups'][$group_index], $arena);
                    $current_cost_delta = $current_cost - $subsolution['cost'];

                    // If this is the first arena or the cost delta is better, select as "best"
                    if ($best_arena === null || $current_cost_delta <= $best_cost_delta) {
                        $best_arena = $arena;
                        $best_arena_index = $arena_index;
                        $best_cost = $current_cost;
                        $best_cost_delta = $current_cost_delta;

                        // If the current cost is zero, no need to look at more groups
                        // No other group can do better
                        if ($current_cost === 0) {
                            break;
                        }
                    }
                }

                $subsolution['arenas'][$group_index] = $best_arena;
                $subsolution['cost'] = $subsolution['cost'] + $best_cost;

                // Remove the chosen arena from the list of available arenas
                unset($arenas[$best_arena_index]);
            }
            $solution[] = $subsolution;
        }
        return $solution;
    }

    public function cost_for_selection($group, $arena)
    {
        $cost = 0;
        foreach ($group as $player_id) {
            if (isset($this->arena_plays[$player_id]) && isset($this->arena_plays[$player_id][$arena])) {
                $cost = $cost + pow($this->arena_plays[$player_id][$arena], 2);

                // Add additional cost for each previously played arena
                // that's been played more than once. This is to avoid
                // a player getting multiple repeat arenas before other
                // players get their first repeat.
                if (isset($this->arena_plays[$player_id])) {
                    foreach ($this->arena_plays[$player_id] as $inner_arena => $inner_count) {
                        if ($inner_arena !== $arena && $inner_count > 0) {
                            $cost += $inner_count;
                        }
                    }
                }
            }
        }

        return $cost;
    }

    // Calculate overall cost for a solution
    public function cost($solution)
    {
        $cost = 0;
        foreach ($solution as $subsolution) {
            $cost += $subsolution['cost'];
        }
        return $cost;
    }

    public function build()
    {
        $result = $this->solve([
            'groups' => $this->groups,
            'available_arenas' => $this->available_arenas
        ]);
        $groups = [];

        // We need to return an array where the index is the group index
        // and the value is an array of selected arenas for that group
        foreach ($this->groups as $group_index => $current_group) {
            $groups[$group_index] = [];
            foreach ($result['solution'] as $subsolution) {
                if (!empty($subsolution['arenas'][$group_index])) {
                    $groups[$group_index][] = $subsolution['arenas'][$group_index];
                } else {
                    $groups[$group_index][] = null;
                }
            }
        }

        ksort($groups);

        return ['cost' => $result['cost'], 'groups' => $groups];
    }
}
