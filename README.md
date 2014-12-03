# Tournament Utilities
This is a handful of utility classes for handling group generation, seeding etc. for pinball match-play tournaments

## ArenaSelector
Pretty naive arena selector. Input an array of arenas and the amount of times each have been played and an array of available arenas (each arena must be an object that implements a `getArenaId` method). The selector will pick a random arena that hasn't been played before or failing that the least played arena of the available ones. Example in `example/ArenaSelector`.

## DanishHeadToHeadPairing
Input an array of seeded players and it'll pair them up according to the Danish system. Seed #1 will play Seed #2, Seed #3 will play seed #4 and so on. Returns an associative array with `groups` and `byes`. The bye will always go to the last seed. Example in `example/DanishHeadToHeadPaiting`.

## HeadToHeadPairing
A somewhat balanced head to head pairing. Takes an array of sub-groups with players and will match up players within in sub-group. Will make a naive attempt at matching players that have played each other the least. Example in `example/HeadToHeadPairing`.

## GroupPairing
Input an array of seeded players and it'll generate 4 player groups based on your seeds and a predefined map of increasingly smaller tiers (so players will be playing opponents at roughly their own level). Just like Pinburgh.

### Visual explanation
* Check out this repository
* Start a webserver
* Run `example/GroupPairing/index.php`

Or go to [http://seeder.slapsave.com/](http://seeder.slapsave.com/)

### Example

```php
    <?php
    require('TournamentUtils/src/GroupPairing.php');

    // Make some fake player objects
    $players_list = array();
    for($i=0;$i<45;$i++) {
      $players_list[] = 'Seed #'.($i+1);
    }

    // Define number of rounds (5 or 10)
    $rounds = 5;

    // Make group builder instance
    $groupBuilder = new haugstrup\TournamentUtils\GroupPairing($rounds, $players_list);

    // Generate groups for all possible rounds
    $round = 0;
    while($round < $groupBuilder->rounds) {
      print "\n------------------------------------------------\n\n";
      print "Round: ".((string)$round)."\n";

      // Actually make the groups
      $groups = $groupBuilder->build($round);

      // Print the groups for this round
      foreach ($groups as $index => $group) {
        print "\nGroup {$index}: ".implode(', ', $group)."\n";
      }

      $round++;
    }
    print "\n------------------------------------------------\n\n";
```
