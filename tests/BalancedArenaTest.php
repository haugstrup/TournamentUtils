<?php

use PHPUnit\Framework\TestCase;

class BalancedArenaTest extends TestCase
{
    public function test_returns_same_number_of_groups()
    {
        $groups = [[1, 2, 3, 4], [5, 6, 7, 8], [9, 10, 11, 12], [13, 14, 15, 16]];
        $arenas = [1, 2, 3, 4, 5, 6, 7, 8];
        $amount = 1;
        $plays = [];
        $builder = new haugstrup\TournamentUtils\BalancedArena($groups, $arenas, $amount, $plays);
        $groups = $builder->build();

        $this->assertEquals(count($groups['groups']), 4);
        $this->assertEquals($groups['cost'], 0);
    }

    public function test_assigns_arena_to_each_group_once()
    {
        $groups = [[1, 2, 3, 4], [5, 6, 7, 8], [9, 10, 11, 12], [13, 14, 15, 16]];
        $arenas = [1, 2, 3, 4, 5, 6, 7, 8];
        $amount = 1;
        $plays = [];
        $builder = new haugstrup\TournamentUtils\BalancedArena($groups, $arenas, $amount, $plays);
        $groups = $builder->build();

        foreach ($groups['groups'] as $group) {
            $this->assertEquals(count($group), $amount);
            $this->assertNotNull($group[0]);
        }
    }

    public function test_assigns_arena_to_each_group_multiple()
    {
        $groups = [[1, 2, 3, 4], [5, 6, 7, 8], [9, 10, 11, 12], [13, 14, 15, 16]];
        $arenas = [1, 2, 3, 4, 5, 6, 7, 8];
        $amount = 2;
        $plays = [];
        $builder = new haugstrup\TournamentUtils\BalancedArena($groups, $arenas, $amount, $plays);
        $groups = $builder->build();

        foreach ($groups['groups'] as $group) {
            $this->assertEquals(count($group), $amount);
            $this->assertNotNull($group[0]);
            $this->assertNotNull($group[1]);
        }
    }

    public function test_respects_skip_list_once()
    {
        $groups = [[1, 2, 3, 4], [5, 6, 7, 8], [9, 10, 11, 12], [13, 14, 15, 16]];
        $arenas = [1, 2, 3, 4];
        $amount = 1;
        $plays = [];
        $skipList = ['3,0'];

        $builder = new haugstrup\TournamentUtils\BalancedArena($groups, $arenas, $amount, $plays);
        $builder->skip_list = $skipList;
        $groups = $builder->build();

        foreach ($groups['groups'] as $group) {
            $this->assertEquals(count($group), $amount);
        }
        $this->assertNull($groups['groups'][3][0]);
    }

    public function test_respects_skip_list_multiple()
    {
        $groups = [[1, 2, 3, 4], [5, 6, 7, 8], [9, 10, 11, 12], [13, 14, 15, 16]];
        $arenas = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
        $amount = 3;
        $plays = [];
        $skipList = ['0,1', '2,2', '3,0', '3,1'];

        $builder = new haugstrup\TournamentUtils\BalancedArena($groups, $arenas, $amount, $plays);
        $builder->skip_list = $skipList;
        $groups = $builder->build();

        foreach ($groups['groups'] as $group) {
            $this->assertEquals(count($group), $amount);
        }
        $this->assertNull($groups['groups'][0][1]);
        $this->assertNull($groups['groups'][2][2]);
        $this->assertNull($groups['groups'][3][0]);
        $this->assertNull($groups['groups'][3][1]);
    }

    public function test_calculates_reasonable_cost()
    {
        // Sample data from: https://app.matchplay.events/tournaments/166935
        // 1: Godzilla
        // 2: Jaws
        // 3: Mandalorian
        // 4: Uncanny X-Men
        // 5: Venom
        $groups = [[1, 2, 3, 4], [5, 6, 7, 8], [9, 10, 11, 12], [13, 14, 15, 16]];
        $arenas = [1, 2, 3, 4, 5];
        $amount = 1;
        $plays = [
            1 => [3 => 3],
            2 => [1 => 1, 4 => 1, 5 => 1],
            3 => [1 => 3],
            4 => [1 => 1, 4 => 1, 5 => 1],
            5 => [2 => 2, 4 => 1],
            6 => [1 => 1, 2 => 1, 3 => 1],
            7 => [4 => 1, 5 => 1, 3 => 1],
            8 => [1 => 1, 3 => 1, 5 => 1],
            9 => [4 => 1, 2 => 1, 1 => 1],
            10 => [2 => 1, 5 => 2],
            11 => [3 => 1, 1 => 1, 4 => 1],
            12 => [1 => 1, 3 => 1, 2 => 1],
            13 => [3 => 1, 5 => 1, 1 => 1],
            14 => [3 => 1, 2 => 1, 5 => 1],
            15 => [2 => 1, 1 => 1, 3 => 1],
            16 => [4 => 2, 3 => 1],
        ];
        $builder = new haugstrup\TournamentUtils\BalancedArena($groups, $arenas, $amount, $plays);
        $groups = $builder->build();

        $this->assertEquals(count($groups['groups']), 4);
        $this->assertEquals($groups['cost'], 6);
    }
}
