<?php

use PHPUnit\Framework\TestCase;

class BalancedGreedyArenaTest extends TestCase
{
    public function test_assigns_arena_to_every_group()
    {
        $groups = [
            [1, 2, 3, 4],
            [5, 6, 7, 8],
            [9, 10, 11, 12],
        ];
        $arenas = [90, 91, 92, 93, 94, 95];
        $amount = 1;

        $builder = new haugstrup\TournamentUtils\BalancedGreedyArena($groups, $arenas, $amount, []);
        $solution = $builder->build();
        foreach ($solution['groups'] as $group) {
            $this->assertEquals(count($group), $amount);
            $this->assertContainsOnly('integer', $group);
        }
        $this->assertEquals(count($solution['groups']), count($groups));
        $this->assertEquals($solution['cost'], 0);
    }

    public function test_does_not_assign_to_skip_list_entry()
    {
        $groups = [
            [1, 2, 3, 4],
            [5, 6, 7, 8],
            [9, 10, 11, 12],
        ];
        $arenas = [90, 91, 92, 93, 94, 95, 96, 97, 98, 99];
        $amount = 3;

        $builder = new haugstrup\TournamentUtils\BalancedGreedyArena($groups, $arenas, $amount, []);
        $builder->skip_list = ['1,1'];
        $solution = $builder->build();
        $this->assertEquals($solution['groups'][1][1], null);
        $this->assertEquals(count($solution['groups']), count($groups));
        $this->assertEquals($solution['cost'], 0);
    }

    public function test_assigns_arena_only_once()
    {
        $groups = [
            [1, 2, 3, 4],
            [5, 6, 7, 8],
            [9, 10, 11, 12],
        ];
        $arenas = [90, 91];
        $amount = 1;

        $builder = new haugstrup\TournamentUtils\BalancedGreedyArena($groups, $arenas, $amount, []);
        $solution = $builder->build();

        $used_arenas = [];
        foreach ($solution['groups'] as $group) {
            $used_arenas[] = $group[0];
        }
        sort($used_arenas);
        $this->assertEquals($used_arenas, [null, 90, 91]);

        $this->assertEquals(count($solution['groups']), count($groups));
        $this->assertEquals($solution['cost'], 0);
    }

    public function test_assigns_arena_once_per_amount()
    {
        $groups = [
            [1, 2, 3, 4],
            [5, 6, 7, 8],
            [9, 10, 11, 12],
        ];
        $arenas = [90, 91];
        $amount = 2;

        $builder = new haugstrup\TournamentUtils\BalancedGreedyArena($groups, $arenas, $amount, []);
        $solution = $builder->build();

        $first_used_arenas = [];
        $second_used_arenas = [];
        foreach ($solution['groups'] as $group) {
            $first_used_arenas[] = $group[0];
            $second_used_arenas[] = $group[1];
        }
        sort($first_used_arenas);
        sort($second_used_arenas);
        $this->assertEquals($first_used_arenas, [null, 90, 91]);
        $this->assertEquals($second_used_arenas, [null, 90, 91]);

        $this->assertEquals(count($solution['groups']), count($groups));
        $this->assertEquals($solution['cost'], 0);
    }

    public function test_does_not_assign_same_arena_more_than_once_to_group()
    {
        $groups = [
            [1, 2, 3, 4],
        ];
        $arenas = [90, 91, 92, 93];
        $arena_plays = [
            // player 1 has played everything except 93
            1 => [90 => 1, 91 => 2, 92 => 2],
        ];
        $amount = 2;

        $builder = new haugstrup\TournamentUtils\BalancedGreedyArena($groups, $arenas, $amount, $arena_plays);
        $solution = $builder->build();

        $sorted = $solution['groups'][0];
        sort($sorted);

        $this->assertEquals($sorted, [90, 93]);
        $this->assertEquals($solution['cost'], 5);
    }

    public function test_calculates_cost_with_no_previous_plays()
    {
        $arena_plays = [];
        $builder = new haugstrup\TournamentUtils\BalancedGreedyArena([], [], 1, $arena_plays);
        $cost = $builder->cost_for_selection([1, 2, 3, 4], 90);
        $this->assertEquals(0, $cost);
    }

    public function test_calculates_cost_with_previous_plays()
    {
        $arena_plays = [
            1 => [90 => 1],
            3 => [90 => 2],
        ];
        $builder = new haugstrup\TournamentUtils\BalancedGreedyArena([], [], 1, $arena_plays);
        $cost = $builder->cost_for_selection([1, 2, 3, 4], 90);
        $this->assertEquals(5, $cost);
    }

    public function test_calculates_cost_with_other_previous_plays()
    {
        $arena_plays = [
            1 => [90 => 1],
            4 => [90 => 2, 91 => 2, 92 => 1],
        ];
        $builder = new haugstrup\TournamentUtils\BalancedGreedyArena([], [], 1, $arena_plays);
        $cost = $builder->cost_for_selection([1, 2, 3, 4], 90);
        $this->assertEquals(8, $cost);
    }
}
