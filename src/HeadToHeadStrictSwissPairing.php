<?php

namespace haugstrup\TournamentUtils;

require_once 'Base.php';
require_once 'MaxWeightMatching.php';

class HeadToHeadStrictSwissPairing extends Base
{
    public $groups = [];

    public $previous_opponents;

    public $byes = [];

    protected $debugEdges = [];

    /**
     * Builds a graph of possible player pairings and assigns a weight to each edge
     * following some simple rules that are somewhat "Swiss" (e.g. minimize repeat pairings,
     * minimize score differences between paired players, assign byes sensibly, ...), then uses a
     * matching algorithm to determine the set of pairings best complying with those rules.
     * The "no repeat pairings" rule of classic Swiss has been somewhat relaxed,
     * so this class can be used for n-strikes knockout tournaments.
     *
     * @param  array  $groups  an array of arrays of player IDs, grouping players who have
     *                         the same number of points (or lives left in a knockout tournament).
     *                         Must be ordered from best (most points, most lives left) to worst.
     * @param  array  $previous_opponents  an associative array with player ID as the key,
     *                                     and as the value an associative array of the number of times an opponent
     *                                     has been faced, keyed by the opponent's player ID.
     * @param  array  $byes  an associative array with player ID as
     *                       the key and the number of byes as the value. This class
     *                       is OK with $byes containing players that are not also in
     *                       $groups, and assumes players not present in $byes have zero byes.
     */
    public function __construct($groups, $previous_opponents, $byes = [])
    {
        $this->groups = $groups;
        $this->previous_opponents = $previous_opponents;
        $this->byes = $byes;
    }

    public function build()
    {
        $pairings = []; // array of 2-element arrays of player IDs
        $byes = []; // array with ID of player with bye, or empty if no bye

        $edges = $debugEdges = $byesLookup = [];
        $playerPseudoscores = $this->getPseudoscores();
        $players = array_keys($playerPseudoscores);

        // Make sure to only consider byes of players that are still active
        foreach ($players as $player) {
            $byesLookup[$player] = $this->byes[$player] ?? 0;
        }
        $maxByesAwardedToOnePlayer = max($byesLookup);
        $allPlayersHadSameNumberOfByes = count(array_unique($byesLookup)) === 1;

        // Calculate the maximum value of a complete matching, disregarding any repeat
        // pairing considerations. Any edges representing repeat pairings will be assigned
        // a penalty larger than this sum (or multiples thereof), so any repeat pairing
        // considerations are guaranteed to be more important than any score based considerations.
        $maxScore = max($playerPseudoscores);
        $maxPairingImportanceFactor = pow($maxScore + 1, 2);
        $maxScoreBasedPenalty = ($maxScore + 1) * $maxPairingImportanceFactor;
        $maxEdgeCount = ceil(count($players) / 2);
        $maxScoreBasedPenaltySum = $maxScoreBasedPenalty * $maxEdgeCount;

        // Only edge weights should have a deterministic effect on the result, so randomize player order
        shuffle($players);

        foreach ($players as $i => $player) {
            if (
                // Only add bye edges if there's actually supposed to be a bye
                count($players) % 2 === 1 &&
                // A player is eligible for a bye only if they don't have more than someone else already
                ($allPlayersHadSameNumberOfByes || ($byesLookup[$player] ?? 0) < $maxByesAwardedToOnePlayer)
            ) {
                // For pairing purposes, the bye is a member of one below the lowest score group, so edges
                // representing a higher ranked player getting the bye will have higher penalties.
                $penalty = pow($playerPseudoscores[$player] + 1, 2) * ($playerPseudoscores[$player] + 1);
                $edges[] = [$i + 1, 0, -$penalty];
                $debugEdges[] = [$player, 'BYE', number_format(-$penalty)];
            }

            $previousPairingCounts = $this->previous_opponents[$player] ?? [];

            foreach ($players as $j => $opponent) {

                // Don't duplicate edges
                if ($j <= $i) {
                    continue;
                }

                // Base edge penalty is the score difference of the pairing
                $scoreDifference = abs($playerPseudoscores[$player] - $playerPseudoscores[$opponent]);
                $penalty = $scoreDifference;

                // Getting fair matches is more important for top players, so the higher ranked
                // one of the players in the pairing is, the more the score difference penalty will be amplified
                $higherScore = max($playerPseudoscores[$player], $playerPseudoscores[$opponent]);
                $pairingImportance = pow($higherScore + 1, 2);
                $penalty *= $pairingImportance;

                // Repeat pairings should be avoided, so they have higher penalties than all the score
                // penalties combined. Since avoiding repeat pairings is more important for top players,
                // the same rank based penalty amplification as before applies.
                $previousPairingCount = $previousPairingCounts[$opponent] ?? 0;
                $repeatPenalty = $previousPairingCount === 0 ? 0 :
                  $maxScoreBasedPenaltySum * pow($maxEdgeCount, $previousPairingCount - 1);
                $penalty += $pairingImportance * $repeatPenalty;

                // Add edge
                $edges[] = [$i + 1, $j + 1, -$penalty];
                $debugEdges[] = [$player, $opponent, number_format(-$penalty)];
            }
        }

        // Store the edge data for debugging purposes
        $this->debugEdges = $debugEdges;

        // Determine the maximum-cardinality matching with the lowest sum of penalties
        $matchingComputer = new \MaxWeightMatching($edges, true);
        $matching = $matchingComputer->main();

        foreach ($matching as $playerIndex => $opponentIndex) {
            // Don't add duplicate pairings
            if ($playerIndex >= $opponentIndex) {
                continue;
            }
            // Player indices were increased by 1 to make room for the bye at 0, now decrease them again
            if ($playerIndex === 0) {
                $byes[] = $players[$opponentIndex - 1];
            } else {
                $pairings[] = [$players[$playerIndex - 1], $players[$opponentIndex - 1]];
            }
        }

        return ['groups' => $pairings, 'byes' => $byes];
    }

    // By looking at the score groups, we know which players have the same score,
    // and whether a group of players has a higher score than another group, but we
    // don't know what score that actually is, so let's call this group membership "pseudoscore".
    public function getPseudoscores()
    {
        $groupsOrderedByAscendingScore = array_reverse($this->groups);
        $playerPseudoscores = [];
        foreach ($groupsOrderedByAscendingScore as $pseudoScore => $group) {
            foreach ($group as $player) {
                $playerPseudoscores[$player] = $pseudoScore;
            }
        }

        return $playerPseudoscores;
    }

    public function getDebugEdges()
    {
        return $this->debugEdges;
    }
}
