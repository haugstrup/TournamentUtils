<?php
use PHPUnit\Framework\TestCase;
use haugstrup\TournamentUtils\RoundRobinPairing;

class RoundRobinPairingTest extends TestCase
{
    const MIN_PLAYERS = 2;
    const MAX_PLAYERS = 30;

    public function testCorrectNumberOfRounds()
    {
        for ($i = static::MIN_PLAYERS; $i <= static::MAX_PLAYERS; $i++) {
            $pairings = $this->getPairings($i);
            $expectedNumberOfRounds = $i % 2 ? $i : $i - 1;
            $this->assertEquals($expectedNumberOfRounds, count($pairings));
        }
    }

  public function testEveryPlayerPlaysAgainstEveryOtherPlayerOnce()
  {
    for ($i = static::MIN_PLAYERS; $i <= static::MAX_PLAYERS; $i++) {
      $pairings = $this->getPairings($i);
      foreach ($this->getPlayers($i) as $player1) {
        foreach ($this->getPlayers($i) as $player2) {
          if ($player1 === $player2) continue;
          $matchCount = 0;
          foreach ($pairings as $round) {
            $groups = $round['groups'];
            foreach ($groups as $group) {
              if (!in_array($player1, $group)) continue;
              if (!in_array($player2, $group)) continue;
              $matchCount++;
            }
          }
          $this->assertEquals(1, $matchCount);
        }
      }
    }
  }

  public function testEveryPlayerPlaysAgainstEveryOtherPlayerTwiceIfDouble()
  {
    for ($i = static::MIN_PLAYERS; $i <= static::MAX_PLAYERS; $i++) {
      $pairings = $this->getPairings($i, true);
      foreach ($this->getPlayers($i) as $player1) {
        foreach ($this->getPlayers($i) as $player2) {
          if ($player1 === $player2) continue;
          $matchCount = 0;
          foreach ($pairings as $round) {
            $groups = $round['groups'];
            foreach ($groups as $group) {
              if (!in_array($player1, $group)) continue;
              if (!in_array($player2, $group)) continue;
              $matchCount++;
            }
          }
          $this->assertEquals(2, $matchCount);
        }
      }
    }
  }

  public function testBalancedPlayerOrder()
    {
        for ($i = static::MIN_PLAYERS; $i <= static::MAX_PLAYERS; $i++) {
            $pairings = $this->getPairings($i);
            $player1Counts = array_fill(0, $i, 0);
            foreach ($pairings as $round) {
                $groups = $round['groups'];
                foreach ($groups as $group) {
                    $player1 = $group[0];
                    $player1Counts[$player1 - 1]++;
                }
            }
            $isNumberOfPlayersEven = $i % 2 === 0;
            $numberOfRounds = $isNumberOfPlayersEven ? $i - 1 : $i;
            $isNumberOfRoundsEven = $numberOfRounds % 2 === 0;
            $expectedPlayer1Counts = $isNumberOfRoundsEven ?
              [$numberOfRounds / 2] :
              [(int) floor($numberOfRounds / 2), (int) ceil($numberOfRounds / 2)];
            foreach ($player1Counts as $player1Count) {
                $this->assertContains($player1Count, $expectedPlayer1Counts);
            }
        }
    }

    public function testBalancedPlayerOrderAgainstSameOpponentIfDouble()
    {
      for ($i = static::MIN_PLAYERS; $i <= static::MAX_PLAYERS; $i++) {
        $pairings = $this->getPairings($i, true);
        foreach ($pairings as $round) {
          $groups = $round['groups'];
          foreach ($this->getPlayers($i) as $player1) {
            foreach ($this->getPlayers($i) as $player2) {
              $matches = array_values(array_filter($groups, function ($group) use ($player1, $player2) {
                return in_array($player1, $group) && in_array($player2, $group);
              }));
              if (count($matches) === 0) continue;
              $this->assertEquals(2, count($matches));
              $this->assertEquals($matches[0], array_reverse($matches[1]));
            }
          }
        }
      }
    }

    protected function getPairings($playerCount, $double = false)
    {
        $players = $this->getPlayers($playerCount);
        $builder = new RoundRobinPairing($players, $double);
        return $builder->build();
    }

    protected function getPlayers($playerCount)
    {
        return range(1, $playerCount);
    }
}
