<?php namespace haugstrup\TournamentUtils;

require_once 'RandomOptimizer.php';

class BalancedArena extends RandomOptimizer {
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
        $this->groups = $groups;
        $this->available_arenas = $available_arenas;
        $this->arena_plays = $arena_plays;
        $this->amount = $amount;
    }

    public function solution($input) {
        // For each group pick a random arena, only allow each arena to be used once
        $solution = [];

        // Shuffle groups so random groups get arena assigned
        // when there are more groups than arenas
        $group_keys = array_keys($input['groups']);
        $group_keys = $this->shuffle($group_keys);
        $input['available_arenas'] = $this->shuffle($input['available_arenas']);

        $arenas = [];
        for($i = 0; $i < $this->amount; $i++) {
            $arenas[$i] = $input['available_arenas'];
        }

        foreach($group_keys as $group_key) {
            $group = $input['groups'][$group_key];
            $chosen_arenas = [];

            foreach ($arenas as $i => $list) {
                $available = $list;
                if (count($chosen_arenas) > 0) {
                    foreach ($available as $j => $a) {
                        if (in_array($a, $chosen_arenas)) {
                            unset($available[$j]);
                        }
                    }
                }

                $chosen_arenas[$i] = null;
                if (count($available) > 0 && !in_array($group_key.','.$i, $this->skip_list)) {
                    $key = $this->array_rand($available, 1);
                    $chosen_arenas[$i] = $available[$key];

                    // Unset from master list
                    foreach ($arenas[$i] as $j => $a) {
                        if ($a == $chosen_arenas[$i]) {
                            unset($arenas[$i][$j]);
                            break;
                        }
                    }
                }
            }

            $solution[] = [
                'group_index' => $group_key,
                'group' => $group,
                'arenas' => $chosen_arenas
            ];
        }

        return $solution;
    }

    public function cost($solution) {
        // Cost is a function of how many plays the arena has gotten
        $cost = 0;

        foreach ($solution as $item) {
            foreach ($item['group'] as $player_id) {
                if (isset($this->arena_plays[$player_id])) {
                    foreach ($item['arenas'] as $arena) {
                        if (isset($this->arena_plays[$player_id][$arena])) {
                            $cost = $cost+pow($this->arena_plays[$player_id][$arena], 2);
                        }
                    }
                }

            }
        }

        return $cost;
    }

    public function build() {
        $result = $this->solve([
            'groups' => $this->groups,
            'available_arenas' => $this->available_arenas
        ]);
        $groups = [];

        foreach ($result['solution'] as $matchup) {
            $groups[$matchup['group_index']] = $matchup['arenas'];
        }

        ksort($groups);

        return ['cost' => $result['cost'], 'groups' => $groups];
    }
}
