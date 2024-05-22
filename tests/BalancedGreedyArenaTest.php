<?php
use PHPUnit\Framework\TestCase;

class BalancedGreedyArenaTest extends TestCase {

    public function testAssignsArenaToEveryGroup() {
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

	public function testCalculatesCostWithNoPreviousPlays() {
		$arena_plays = [];
        $builder = new haugstrup\TournamentUtils\BalancedGreedyArena([], [], 1, $arena_plays);
        $cost = $builder->cost_for_selection([1, 2, 3, 4], 90);
        $this->assertEquals(0, $cost);
    }

	public function testCalculatesCostWithPreviousPlays() {
		$arena_plays = [
			1 => [90 => 1],
			3 => [90 => 2],
		];
        $builder = new haugstrup\TournamentUtils\BalancedGreedyArena([], [], 1, $arena_plays);
        $cost = $builder->cost_for_selection([1, 2, 3, 4], 90);
        $this->assertEquals(5, $cost);
    }

	public function testCalculatesCostWithOtherPreviousPlays() {
		$arena_plays = [
			1 => [90 => 1],
			4 => [90 => 2, 91 => 2, 92 => 1],
		];
        $builder = new haugstrup\TournamentUtils\BalancedGreedyArena([], [], 1, $arena_plays);
        $cost = $builder->cost_for_selection([1, 2, 3, 4], 90);
        $this->assertEquals(8, $cost);
    }

}
