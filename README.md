# Group builder for match-play tournaments
This is very much a work in progress. I made this for pinball match-play pinball tournaments. Input an array of seeded players and it'll generate 4 player groups based on your seeds and a predefined map of increasingly smaller tiers (so players will be playing opponents at roughly their own level).

# Visual explanation
* Check out this repository
* Start a webserver
* Run `example/index.php`

Or go to [http://seeder.slapsave.com/](http://seeder.slapsave.com/)

# Example

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
