<?php
use PHPUnit\Framework\TestCase;

class BalancedArenaTest extends TestCase {

  	public function testReturnsSameNumberOfGroups() {
	    $groups = [[1, 2, 3, 4], [5, 6, 7, 8], [9, 10, 11, 12], [13, 14, 15, 16]];
		$arenas = [1, 2, 3, 4, 5, 6, 7, 8];
		$amount = 1;
		$plays = [];
    	$builder = new haugstrup\TournamentUtils\BalancedArena($groups, $arenas, $amount, $plays);
    	$groups = $builder->build();

    	$this->assertEquals(count($groups['groups']), 4);
    	$this->assertEquals($groups['cost'], 0);
	}

	public function testAssignsArenaToEachGroupOnce() {
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

	public function testAssignsArenaToEachGroupMultiple() {
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

	public function testRespectsSkipListOnce() {
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

	public function testRespectsSkipListMultiple() {
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
}
