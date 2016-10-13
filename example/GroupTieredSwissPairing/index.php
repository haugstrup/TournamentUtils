<!DOCTYPE html>
<html>
  <head>
    <title>Match play groups</title>
    <link rel="stylesheet" href="bootstrap.min.css">
    <style>
      .container {
        width:800px;
        margin:40px auto;
      }
    </style>
  </head>
  <body>
    <div class="container">
      <h1>Match play group generation</h1>
      <p>Select a number of players below to see how groups will be generated for that amount of players. This is the same seeding system as used for Pinburgh and Pinball at the Lake. Current 16 to 128 players is supported along with tiers for 3, 4, 5 or 10 rounds of play.</p>
      <form action="/" method="get" class="form-inline">
          <select name="player_count" class="form-control">
            <option value="">Number of players...</option>
            <?php
              for ($i=128;$i>=16;$i--) {
                $selected = !empty($_GET['player_count']) && (int)$_GET['player_count'] === $i ? 'selected' : '';
                print "<option {$selected} value='{$i}'>{$i} players</option>";
              }
            ?>
          </select>
          <select name="rounds" class="form-control">
            <option value="">Number of rounds...</option>
            <option value="3" <?php print !empty($_GET['rounds']) && (int)$_GET['rounds'] === 3 ? 'selected' : '' ?>>3 rounds</option>
            <option value="4" <?php print !empty($_GET['rounds']) && (int)$_GET['rounds'] === 4 ? 'selected' : '' ?>>4 rounds</option>
            <option value="5" <?php print !empty($_GET['rounds']) && (int)$_GET['rounds'] === 5 ? 'selected' : '' ?>>5 rounds</option>
            <option value="10" <?php print !empty($_GET['rounds']) && (int)$_GET['rounds'] === 10 ? 'selected' : '' ?>>10 rounds</option>
            <option value="12" <?php print !empty($_GET['rounds']) && (int)$_GET['rounds'] === 12 ? 'selected' : '' ?>>12 rounds</option>
            <option value="13" <?php print !empty($_GET['rounds']) && (int)$_GET['rounds'] === 13 ? 'selected' : '' ?>>13 rounds</option>
          </select>
          <button type="submit" class="form-control">Go!</button>
      </form>

      <?php
      require('../../src/GroupTieredSwissPairing.php');

      if (!empty($_GET['player_count']) && is_numeric($_GET['player_count']) && !empty($_GET['rounds']) && is_numeric($_GET['rounds'])) {
        $player_count = (int)$_GET['player_count'];
        $players_list = array();
        for($i=0;$i<$player_count;$i++) {
          $players_list[] = 'Seed #'.($i+1);
        }
        $groupBuilder = new haugstrup\TournamentUtils\GroupTieredSwissPairing((int)$_GET['rounds'], $players_list);
        $group_map = $groupBuilder->get_group_map();

        print "<hr><h2>Tier sizes for {$player_count} players (using tiers for {$group_map['key']} players)</h2>";
        $round = 0;

        $unit = 100/$group_map['key'];
        print "<table class='table table-bordered'>";
        while($round < $groupBuilder->rounds) {
          $map = $groupBuilder->get_round_map($round);
          $id = $round+1;
          print "<tr><th style='width:150px;'>Round {$id}</th><td>";
          foreach($map as $m) {
            print "<div class='text-center' style='box-sizing:border-box;border-left:2px solid white;border-right:2px solid white;background:#ccc;display:inline-block;width:".($unit*$m)."%;'>{$m}</div>";
          }

          print "</td></tr>";
          $round++;
        }
        print "</table>";

        print "<h2>Groups for {$player_count} players</h2>";

        // Generate groups for all possible rounds
        $round = 0;
        while($round < $groupBuilder->rounds) {
          $id = $round+1;
          print "<section>";
          print "<h3>Round: ".($id)."</h3>";

          $groups = $groupBuilder->build($round);

          print "<table class='table table-bordered table-condensed'>";
          foreach ($groups as $index => $group) {
            $id = $index+1;
            print "<tr><th>Group {$id}</th>";
            foreach ($group as $p) {
              print "<td class='text-center'>{$p}</td>";
            }
            print "</tr>";
          }
          print "</table>";

          print "</section>";

          $round++;
        }
      }
      ?>
    </div>
  </body>
</html>
