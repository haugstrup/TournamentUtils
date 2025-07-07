<?php

use PHPUnit\Framework\TestCase;

class WCSGroupsTest extends TestCase
{
    public function test_creates_correct_number_of_groups()
    {
        $players = ['Seed#1', 'Seed#2', 'Seed#3', 'Seed#4', 'Seed#5', 'Seed#6', 'Seed#7', 'Seed#8'];
        $builder = new haugstrup\TournamentUtils\WCSGroups($players, 2);
        $groups = $builder->build();
        $this->assertEquals(4, count($groups));

        $players = ['Seed#1', 'Seed#2', 'Seed#3', 'Seed#4', 'Seed#5', 'Seed#6', 'Seed#7', 'Seed#8'];
        $builder = new haugstrup\TournamentUtils\WCSGroups($players, 4);
        $groups = $builder->build();
        $this->assertEquals(2, count($groups));

        $players = ['Seed#1', 'Seed#2', 'Seed#3', 'Seed#4', 'Seed#5', 'Seed#6', 'Seed#7', 'Seed#8'];
        $builder = new haugstrup\TournamentUtils\WCSGroups($players, 6);
        $groups = $builder->build();
        $this->assertEquals(1, count($groups));

        $players = ['Seed#1', 'Seed#2', 'Seed#3', 'Seed#4', 'Seed#5', 'Seed#6', 'Seed#7', 'Seed#8'];
        $builder = new haugstrup\TournamentUtils\WCSGroups($players, 8);
        $groups = $builder->build();
        $this->assertEquals(1, count($groups));

        $players = ['Seed#1', 'Seed#2', 'Seed#3', 'Seed#4', 'Seed#5', 'Seed#6', 'Seed#7', 'Seed#8'];
        $builder = new haugstrup\TournamentUtils\WCSGroups($players, 10);
        $groups = $builder->build();
        $this->assertEquals(1, count($groups));

        $players = ['Seed#1', 'Seed#2', 'Seed#3', 'Seed#4', 'Seed#5', 'Seed#6', 'Seed#7', 'Seed#8', 'Seed#9', 'Seed#10', 'Seed#11', 'Seed#12', 'Seed#13', 'Seed#14', 'Seed#15', 'Seed#16'];
        $builder = new haugstrup\TournamentUtils\WCSGroups($players, 4);
        $groups = $builder->build();
        $this->assertEquals(4, count($groups));
    }

    public function test_places_seeds()
    {
        $players = [
            'Seed#1',
            'Seed#2',
            'Seed#3',
            'Seed#4',

            'Seed#5',
            'Seed#6',
            'Seed#7',
            'Seed#8',

            'Seed#9',
            'Seed#10',
            'Seed#11',
            'Seed#12',

            'Seed#13',
            'Seed#14',
            'Seed#15',
            'Seed#16',
        ];
        $builder = new haugstrup\TournamentUtils\WCSGroups($players, 4);
        $groups = $builder->build();

        $this->assertEquals(['Seed#1', 'Seed#8', 'Seed#12', 'Seed#16'], $groups[0]);
        $this->assertEquals(['Seed#2', 'Seed#7', 'Seed#11', 'Seed#15'], $groups[1]);
        $this->assertEquals(['Seed#3', 'Seed#6', 'Seed#10', 'Seed#14'], $groups[2]);
        $this->assertEquals(['Seed#4', 'Seed#5', 'Seed#9', 'Seed#13'], $groups[3]);
    }

    public function test_places_extras()
    {
        $players = [
            'Seed#1',
            'Seed#2',
            'Seed#3',
            'Seed#4',

            'Seed#5',
            'Seed#6',
            'Seed#7',
            'Seed#8',

            'Seed#9',
            'Seed#10',
            'Seed#11',
            'Seed#12',

            'Seed#13',
            'Seed#14',
            'Seed#15',
            'Seed#16',

            'Seed#17',
            'Seed#18',
            'Seed#19',
        ];
        $builder = new haugstrup\TournamentUtils\WCSGroups($players, 4);
        $groups = $builder->build();

        $this->assertEquals(['Seed#1', 'Seed#8', 'Seed#12', 'Seed#16'], $groups[0]);
        $this->assertEquals(['Seed#2', 'Seed#7', 'Seed#11', 'Seed#15', 'Seed#19'], $groups[1]);
        $this->assertEquals(['Seed#3', 'Seed#6', 'Seed#10', 'Seed#14', 'Seed#18'], $groups[2]);
        $this->assertEquals(['Seed#4', 'Seed#5', 'Seed#9', 'Seed#13', 'Seed#17'], $groups[3]);
    }

    public function test_places_extras_from_top()
    {
        $players = [
            'Seed#1',
            'Seed#2',
            'Seed#3',
            'Seed#4',

            'Seed#5',
            'Seed#6',
            'Seed#7',
            'Seed#8',

            'Seed#9',
            'Seed#10',
            'Seed#11',
            'Seed#12',

            'Seed#13',
            'Seed#14',
            'Seed#15',
            'Seed#16',

            'Seed#17',
            'Seed#18',
            'Seed#19',
        ];
        $builder = new haugstrup\TournamentUtils\WCSGroups($players, 4, false);
        $groups = $builder->build();

        $this->assertEquals(['Seed#1', 'Seed#8', 'Seed#12', 'Seed#16', 'Seed#17'], $groups[0]);
        // $this->assertEquals(['Seed#2', 'Seed#7', 'Seed#11', 'Seed#15', 'Seed#18'], $groups[1]);
        // $this->assertEquals(['Seed#3', 'Seed#6', 'Seed#10', 'Seed#14', 'Seed#19'], $groups[2]);
        // $this->assertEquals(['Seed#4', 'Seed#5', 'Seed#9', 'Seed#13'], $groups[3]);
    }

    public function test_no_players()
    {
        $players = [];
        $builder = new haugstrup\TournamentUtils\WCSGroups($players, 4);
        $groups = $builder->build();

        $this->assertEquals(0, count($groups));
    }
}
