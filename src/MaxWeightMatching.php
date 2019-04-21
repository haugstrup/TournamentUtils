<?php

/*

Weighted maximum matching in general graphs.

http://bluebones.net/blossom-algorithm-in-php

This is a direct conversion of Joris van Rantwijk’s python code with
the same tests and the same output. See
http://jorisvr.nl/article/maximum-matching

The algorithm is taken from "Efficient Algorithms for Finding Maximum
Matching in Graphs" by Zvi Galil, ACM Computing Surveys, 1986.
It is $based on the "blossom" method for finding augmenting $paths and
the "primal-dual" method for finding a matching of maximum weight, both
due to Jack Edmonds.
Some ideas came from "Implementation of algorithms for maximum matching
on non-bipartite graphs" by H.J. Gabow, Standford Ph.D. thesis, 1973.

A C program for maximum weight matching by Ed Rothberg was used extensively
to validate this new code.

*/

# If assigned, $DEBUG(str) is called with lots of $debug messages.
$DEBUG = null;
// $DEBUG = function($s) {
//     error_log("DEBUG: $s");
// };

# Check $delta2/delta3 computation after every substage;
# only works on integer weights, slows down the algorithm to O(n^4).
$CHECK_DELTA = false;

# Check optimality of solution before returning; only works on integer weights.
$CHECK_OPTIMUM = true;

function maxWeightMatching($edges, $maxcardinality=false) {
    global $DEBUG;
    $o = new MaxWeightMatching($edges, $maxcardinality);
    return $o->main();
}

class MaxWeightMatching {
    /*
    Compute a maximum-weighted matching in the general undirected
    weighted graph given by "edges".  If "$maxcardinality" is true,
    only maximum-cardinality matchings are considered as solutions.

    Edges is a sequence of tuples ($i, $j, $wt) describing an undirected
    edge between vertex $i && vertex $j with weight wt.  There is at most
    one edge between any two vertices; no vertex has an edge to itself.
    Vertices are identified by consecutive, non-negative integers.

    Return a list "mate", such that $this->mate[$i] == $j if vertex $i is
    matched to vertex j, && $this->mate[$i] == -1 if vertex $i is not matched.

    This function takes time O(n ** 3)."""
    global $DEBUG;

    #
    # Vertices are numbered 0 .. ($this->nvertex-1).
    # Non-trivial blossoms are numbered $this->nvertex .. (2*$this->nvertex-1)
    #
    # Edges are numbered 0 .. (nedge-1).
    # Edge $this->endpoints are numbered 0 .. (2*nedge-1), such that $this->endpoints
    # (2*k) && (2*k+1) both belong to edge k.
    #
    # Many terms used in the comments (sub-blossom, T-vertex) come from
    # the paper by Galil; read the paper before reading this code.
    #

    */

    function __construct($edges, $maxcardinality=false) {
        global $DEBUG;

        $this->edges = $edges;
        $this->maxcardinality = $maxcardinality;

        # Deal swiftly with empty graphs.
        if (!$this->edges) {
            return;
        }

        # Count vertices.
        $this->nedge = count($this->edges);
        $this->nvertex = 0;
        foreach ($this->edges as $edge) {
            list($i, $j, $w) = $edge;
            assert($i >= 0 && $j >= 0 && $i != $j);
            if ($i >= $this->nvertex) {
                $this->nvertex = $i + 1;
            }
            if ($j >= $this->nvertex) {
                $this->nvertex = $j + 1;
            }
        }

        # Find the maximum edge weight.
        $weights = [];
        foreach ($this->edges as $edge) {
            list($i, $j, $wt) = $edge;
            $weights[] = $wt;
        }
        $maxweight = max(0, max($weights));

        # If p is an edge $this->endpoint,
        # $this->endpoint[$p] is the vertex to which $this->endpoint p is attached.
        # Not modified by the algorithm.
        $this->endpoint = [];
        foreach (range(0, 2 * $this->nedge - 1) as $p) { // BAKERT range is one higher in python so -1 here and everywhere else we use range().
            $this->endpoint[] = $this->edges[$this->floorintdiv($p, 2)][$p % 2];
        }

        # If $v is a vertex,
        # $this->neighbend[$v] is the list of remote $this->endpoints of the edges attached to v.
        # Not modified by the algorithm.
        $this->neighbend = [];
        foreach (range(0, $this->nvertex - 1) as $_) {
            $this->neighbend[] = [];
        }
        foreach (range(0, count($this->edges) - 1) as $k) {
            list($i, $j, $w) = $this->edges[$k];
            $this->neighbend[$i][] = 2 * $k + 1;
            $this->neighbend[$j][] = 2 * $k;
        }

        # If $v is a vertex,
        # $this->mate[$v] is the remote $this->endpoint of its matched edge, || -1 if it is single
        # (i.e. $this->endpoint[mate[$v]] is v's partner vertex).
        # Initially all vertices are single; updated during augmentation.
        $this->mate = array_fill(0, $this->nvertex, -1);

        # If $b is a top-level blossom,
        # $this->label[$b] is 0 if $b is unlabeled (free);
        #             1 if $b is an S-vertex/blossom;
        #             2 if $b is a T-vertex/blossom.
        # The $this->label of a vertex is found by looking at the $this->label of its
        # top-level containing blossom.
        # If $v is a vertex inside a T-blossom,
        # $this->label[$v] is 2 iff $v is reachable from an S-vertex outside the blossom.
        # $this->labels are assigned during a stage && reset after each augmentation.
        $this->label = array_fill(0, 2 * $this->nvertex, 0);

        # If $b is a $this->labeled top-level blossom,
        # $this->labelend[$b] is the remote $this->endpoint of the edge through which $b obtained
        # its $this->label, || -1 if b's $base vertex is single.
        # If $v is a vertex inside a T-blossom && $this->label[$v] == 2,
        # $this->labelend[$v] is the remote $this->endpoint of the edge through which $v is
        # reachable from outside the blossom.
        $this->labelend = array_fill(0, 2 * $this->nvertex, -1);

        # If $v is a vertex,
        # $this->inblossom[$v] is the top-level blossom to which $v belongs.
        # If $v is a top-level vertex, $v is itself a blossom (a trivial blossom)
        # && $this->inblossom[$v] == v.
        # Initially all vertices are top-level trivial blossoms.
        $this->inblossom = range(0, $this->nvertex - 1);

        # If $b is a sub-blossom,
        # $this->blossomparent[$b] is its immediate parent (sub-)blossom.
        # If $b is a top-level blossom, $this->blossomparent[$b] is -1.
        $this->blossomparent = array_fill(0, 2 * $this->nvertex, -1);

        # If $b is a non-trivial (sub-)blossom,
        # $this->blossomchilds[$b] is an ordered list of its sub-blossoms, starting with
        # the $base && going round the blossom.
        $this->blossomchilds = array_fill(0, 2 * $this->nvertex, null);

        # If $b is a (sub-)blossom,
        # $this->blossombase[$b] is its $base VERTEX (i.e. recursive sub-blossom).
        $this->blossombase = array_merge(range(0, $this->nvertex - 1), array_fill(0, $this->nvertex, -1));

        # If $b is a non-trivial (sub-)blossom,
        # $this->blossomendps[$b] is a list of $this->endpoints on its connecting edges,
        # such that $this->blossomendps[$b][$i] is the local $this->endpoint of $this->blossomchilds[$b][$i]
        # on the edge that connects it to $this->blossomchilds[$b][wrap(i+1)].
        $this->blossomendps = array_fill(0, 2 * $this->nvertex, null);

        # If $v is a free vertex (or an unreached vertex inside a T-blossom),
        # $this->bestedge[$v] is the edge to an S-vertex with least slack,
        # || -1 if there is no such edge.
        # If $b is a (possibly trivial) top-level S-blossom,
        # $this->bestedge[$b] is the least-slack edge to a different S-blossom,
        # || -1 if there is no such edge.
        # This is used for efficient computation of $delta2 && $delta3.
        $this->bestedge = array_fill(0, 2 * $this->nvertex, -1);

        # If $b is a non-trivial top-level S-blossom,
        # $this->blossombestedges[$b] is a list of least-slack edges to neighbouring
        # S-blossoms, || null if no such list has been computed yet.
        # This is used for efficient computation of $delta3.
        $this->blossombestedges = array_fill(0, 2 * $this->nvertex, null);

        # List of currently unused blossom numbers.
        $this->unusedblossoms = range($this->nvertex, 2 * $this->nvertex - 1);

        # If $v is a vertex,
        # $this->dualvar[$v] = 2 * u(v) where u(v) is the v's variable in the dual
        # optimization problem (multiplication by two ensures integer values
        # throughout the algorithm if all edge weights are integers).
        # If $b is a non-trivial blossom,
        # $this->dualvar[$b] = z($b) where z($b) is b's variable in the dual optimization
        # problem.
        $this->dualvar = array_merge(array_fill(0, $this->nvertex, $maxweight), array_fill(0, $this->nvertex, 0));

        # If $this->allowedge[$k] is true, edge k has zero slack in the optimization
        # problem; if $this->allowedge[$k] is false, the edge's slack may || may not
        # be zero.
        $this->allowedge = array_fill(0, $this->nedge, false);

        # Queue of newly discovered S-vertices.
        $this->queue = [];
    }

    # Return 2 * slack of edge k (does not work inside blossoms).
    function slack($k) {
        global $DEBUG;
        list($i, $j, $wt) = $this->safe_access($this->edges, $k);
        return $this->dualvar[$i] + $this->dualvar[$j] - 2 * $wt;
    }

    # Generate the leaf vertices of a blossom.
    function blossomLeaves($b) {
        global $DEBUG;
        if ($b < $this->nvertex) {
            yield $b;
        } else {
            foreach ($this->blossomchilds[$b] as $t) {
                if ($t < $this->nvertex) {
                    yield $t;
                } else {
                    foreach ($this->blossomLeaves($t) as $v) {
                        yield $v;
                    }
                }
            }
        }
    }

    # Assign $this->label t to the top-level blossom containing vertex w
    # && record the fact that $w was reached through the edge with
    # remote $this->endpoint p.
    function assignLabel($w, $t, $p) {
        global $DEBUG;
        if ($DEBUG) {
            $DEBUG("assignLabel($w,$t,$p)");
        }
        $b = $this->inblossom[$w];
        assert($this->label[$w] == 0 && $this->label[$b] == 0);
        $this->label[$w] = $this->label[$b] = $t; # BAKERT issue ???
        $this->labelend[$w] = $this->labelend[$b] = $p;
        $this->bestedge[$w] = $this->bestedge[$b] = -1;
        if ($t == 1) {
            # $b became an S-vertex/blossom; add it(s vertices) to the queue.
            foreach ($this->blossomLeaves($b) as $leaf) {
                $this->queue[] = $leaf;
                if ($DEBUG) {
                    $DEBUG("PUSH $leaf");
                }
            }
        } elseif ($t == 2) {
            # $b became a T-vertex/blossom; assign $this->label $S to its $this->mate.
            # (If $b is a non-trivial blossom, its $base is the only vertex
            # with an external $this->mate.)
            $base = $this->blossombase[$b];
            assert($this->mate[$base] >= 0);
            $this->assignLabel($this->endpoint[$this->mate[$base]], 1, $this->mate[$base] ^ 1);
        }
    }

    # Trace back from vertices $v && $w to discover either a new blossom
    # || an augmenting $path. Return the $base vertex of the new blossom || -1.
    function scanBlossom($v, $w) {
        global $DEBUG;
        if ($DEBUG) {
            $DEBUG("scanBlossom($v,$w)");
        }
        # Trace back from $v && w, placing breadcrumbs as we go.
        $path = [];
        $base = -1;
        while ($v != -1 || $w != -1) {
            # Look for a breadcrumb in v's blossom || put a new breadcrumb.
            $b = $this->inblossom[$v];
            if ($this->label[$b] & 4) {
                $base = $this->blossombase[$b];
                break;
            }
            assert($this->label[$b] == 1);
            $path[] = $b;
            $this->label[$b] = 5;
            # Trace one step back.
            assert($this->labelend[$b] == $this->mate[$this->blossombase[$b]]);
            if ($this->labelend[$b] == -1) {
                # The $base of blossom $b is single; stop tracing this $path.
                $v = -1;
            } else {
                $v = $this->endpoint[$this->labelend[$b]];
                $b = $this->inblossom[$v];
                assert($this->label[$b] == 2);
                # $b is a T-blossom; trace one more step back.
                assert($this->labelend[$b] >= 0);
                $v = $this->endpoint[$this->labelend[$b]];
            }
            # Swap $v && $w so that we alternate between both $paths.
            if ($w != -1) {
                list($v, $w) = [$w, $v];
            }
        }
        # Remove breadcrumbs.
        foreach ($path as $b) {
            $this->label[$b] = 1;
        }
        # Return $base vertex, if we found one.
        return $base;
    }

    # Construct a new blossom with given $base, containing edge k which
    # connects a pair of $S vertices. $this->label the new blossom as S; set its dual
    # variable to zero; relabel its T-vertices to $S && add them to the queue.
    function addBlossom($base, $k) {
        global $DEBUG;
        list($v, $w, $wt) = $this->edges[$k];
        $bb = $this->inblossom[$base];
        $bv = $this->inblossom[$v];
        $bw = $this->inblossom[$w];
        # Create blossom.
        $b = array_pop($this->unusedblossoms);
        if ($DEBUG) {
            $DEBUG("addBlossom($base,$k) (v=$v w=$w) -> $b");
        }
        $this->blossombase[$b] = $base;
        $this->blossomparent[$b] = -1;
        $this->blossomparent[$bb] = $b;
        # Make list of sub-blossoms && their interconnecting edge $this->endpoints.
        $this->blossomchilds[$b] = [];
        $this->blossomendps[$b] = [];
        # Trace back from $v to $base.
        while ($bv != $bb) {
            # Add $bv to the new blossom.
            $this->blossomparent[$bv] = $b;
            $this->blossomchilds[$b][] = $bv;
            $this->blossomendps[$b][] = $this->labelend[$bv];
            assert($this->label[$bv] == 2 ||
                    ($this->label[$bv] == 1 && $this->labelend[$bv] == $this->mate[$this->blossombase[$bv]]));
            # Trace one step back.
            assert($this->labelend[$bv] >= 0);
            $v = $this->endpoint[$this->labelend[$bv]];
            $bv = $this->inblossom[$v];
        }
        # Reverse lists, add $this->endpoint that connects the pair of $S vertices.
        $this->blossomchilds[$b][] = $bb;
        $this->blossomchilds[$b] = array_reverse($this->blossomchilds[$b]);
        $this->blossomendps[$b] = array_reverse($this->blossomendps[$b]);
        $this->blossomendps[$b][] = 2 * $k;
        # Trace back from $w to $base.
        while ($bw != $bb) {
            # Add $bw to the new blossom.
            $this->blossomparent[$bw] = $b;
            $this->blossomchilds[$b][] = $bw;
            $this->blossomendps[$b][] = $this->labelend[$bw] ^ 1;
            assert($this->label[$bw] == 2 ||
                   ($this->label[$bw] == 1 && $this->labelend[$bw] == $this->mate[$this->blossombase[$bw]]));
            # Trace one step back.
            assert($this->labelend[$bw] >= 0);
            $w = $this->endpoint[$this->labelend[$bw]];
            $bw = $this->inblossom[$w];
        }
        # Set $this->label to S.;
        assert($this->label[$bb] == 1);
        $this->label[$b] = 1;
        $this->labelend[$b] = $this->labelend[$bb];
        # Set dual variable to zero.
        $this->dualvar[$b] = 0;
        # Relabel vertices.
        foreach ($this->blossomLeaves($b) as $v) {
            if ($this->label[$this->inblossom[$v]] == 2) {
                # This T-vertex now turns into an S-vertex because itbecoms;
                # part of an S-blossom; add it to the queue.
                $this->queue[] = $v;
            }
            $this->inblossom[$v] = $b;
        }
        # Compute $this->blossombestedges[$b].
        $this->bestedgeto = array_fill(0, 2 * $this->nvertex, -1);
        foreach ($this->blossomchilds[$b] as $bv) {
            if ($this->blossombestedges[$bv] === null) {
                # This subblossom does not have a list of least-slack edges;
                # get the information from the vertices.
                $nblists = [];
                foreach ($this->blossomLeaves($bv) as $v) {
                    $nblist = [];
                    foreach ($this->neighbend[$v] as $p) {
                        $nblist[] = $this->floorintdiv($p, 2);
                    }
                    $nblists[] = $nblist;
                }
            } else {
                # Walk this subblossom's least-slack edges.
                $nblists = [$this->blossombestedges[$bv]];
            }
            foreach ($nblists as $nblist) {
                foreach ($nblist as $k) {
                    list($i, $j, $wt) = $this->edges[$k];
                    if ($this->inblossom[$j] == $b) {
                        list($i, $j) = [$j, $i];
                    }
                    $bj = $this->inblossom[$j];
                    if ($bj != $b && $this->label[$bj] == 1 &&
                        ($this->bestedgeto[$bj] == -1 ||
                        $this->slack($k) < $this->slack($this->bestedgeto[$bj]))) {
                        $this->bestedgeto[$bj] = $k;
                    }
                }
            }
            # Forget about least-slack edges of the subblossom.
            $this->blossombestedges[$bv] = null;
            $this->bestedge[$bv] = -1;
        }
        $this->blossombestedges[$b] = [];
        foreach ($this->bestedgeto as $k) {
            if ($k != -1) {
                $this->blossombestedges[$b][] = $k;
            }
        }

        # Select $this->bestedge[$b].
        $this->bestedge[$b] = -1;
        foreach ($this->blossombestedges[$b] as $k) {
            if ($this->bestedge[$b] == -1 || $this->slack($k) < $this->slack($this->bestedge[$b])) {
                $this->bestedge[$b] = $k;
            }
        }
        if ($DEBUG) {
            $DEBUG("blossomchilds[$b]=" . $this->arr_repr($this->blossomchilds[$b]));
        }
    }

    # Expand the given top-level blossom.
    function expandBlossom($b, $endstage) {
        global $DEBUG;
        if ($DEBUG) {
            $DEBUG("expandBlossom($b,$endstage) " . $this->arr_repr($this->blossomchilds[$b]));
        }
        # Convert sub-blossoms into top-level blossoms.
        foreach ($this->blossomchilds[$b] as $s) {
            $this->blossomparent[$s] = -1;
            if ($s < $this->nvertex) {
                $this->inblossom[$s] = $s;
            } elseif ($endstage && $this->dualvar[$s] == 0) {
                # Recursively expand this sub-blossom.
                $this->expandBlossom($s, $endstage);
            } else {
                foreach ($this->blossomLeaves($s) as $v) {
                    $this->inblossom[$v] = $s;
                }
            }
        }
        # If we expand a T-blossom during a stage, its sub-blossoms must be
        # relabeled.
        if ((!$endstage) && $this->label[$b] == 2) {
            # Start at the sub-blossom through which the expanding
            # blossom obtained its $this->label, && relabel sub-blossoms untili
            # we reach the $base.
            # Figure out through which sub-blossom the expanding blossom
            # obtained its $this->label initially.
            assert($this->labelend[$b] >= 0);
            $entrychild = $this->inblossom[$this->endpoint[$this->labelend[$b] ^ 1]];
            # Decide in which direction we will go round the blossom.
            $j = array_search($entrychild, $this->blossomchilds[$b]);
            if ($j & 1) {
                # Start index is odd; go forward && wrap.
                $j -= count($this->blossomchilds[$b]);
                $jstep = 1;
                $endptrick = 0;
            } else {
                # Start index is even; go backward.
                $jstep = -1;
                $endptrick = 1;
            }
            # Move along the blossom until we get to the $base.
            $p = $this->labelend[$b];
            while ($j != 0) {
                # Relabel the T-sub-blossom.
                $this->label[$this->endpoint[$p ^ 1]] = 0;
                $safe_index = $this->safe_index($this->blossomendps[$b], $j - $endptrick);
                $this->label[$this->endpoint[$this->blossomendps[$b][$safe_index] ^ $endptrick ^ 1]] = 0;
                $this->assignLabel($this->endpoint[$p ^ 1], 2, $p);
                # Step to the next S-sub-blossom && note its forward $this->endpoint.
                $safe_index = $this->safe_index($this->blossomendps[$b], $j - $endptrick);
                $this->allowedge[$this->floorintdiv($this->blossomendps[$b][$safe_index], 2)] = true;
                $j += $jstep;
                $safe_index = $this->safe_index($this->blossomendps[$b], $j - $endptrick);
                $p = $this->blossomendps[$b][$safe_index] ^ $endptrick;
                # Step to the next T-sub-blossom.
                $this->allowedge[$this->floorintdiv($p, 2)] = true;
                $j += $jstep;
            }
            # Relabel the $base T-sub-blossom WITHOUT stepping through to
            # its $this->mate (so don't call assignLabel).
            $bv = $this->blossomchilds[$b][$j];
            $this->label[$this->endpoint[$p ^ 1]] = $this->label[$bv] = 2;
            $this->labelend[$this->endpoint[$p ^ 1]] = $this->labelend[$bv] = $p;
            $this->bestedge[$bv] = -1;
            # Continue along the blossom until we get back to $entrychild.
            $j += $jstep;
            while ($this->safe_access($this->blossomchilds[$b], $j) != $entrychild) {
                # Examine the vertices of the sub-blossom to see whether
                # it is reachable from a neighbouring S-vertex outside the
                # expanding blossom.
                $bv = $this->safe_access($this->blossomchilds[$b], $j);
                if ($this->label[$bv] == 1) {
                    # This sub-blossom just got $this->label $S through one of its
                    # neighbours; leave it.
                    $j += $jstep;
                    continue;
                }
                foreach ($this->blossomLeaves($bv) as $v) {
                    if ($this->label[$v] != 0) {
                        break;
                    }
                }
                # If the sub-blossom contains a reachable vertex, assign
                # $this->label T to the sub-blossom.
                if ($this->label[$v] != 0) {
                    assert($this->label[$v] == 2);
                    assert($this->inblossom[$v] == $bv);
                    $this->label[$v] = 0;
                    $this->label[$this->endpoint[$this->mate[$this->blossombase[$bv]]]] = 0;
                    $this->assignLabel($v, 2, $this->labelend[$v]);
                }
                $j += $jstep;
            }
        }
        # Recycle the blossom number.
        $this->label[$b] = $this->labelend[$b] = -1;
        $this->blossomchilds[$b] = $this->blossomendps[$b] = null;
        $this->blossombase[$b] = -1;
        $this->blossombestedges[$b] = null;
        $this->bestedge[$b] = -1;
        $this->unusedblossoms[] = $b;
    }

    # Swap matched/unmatched edges over an alternating $path throughblossomb;
    # between vertex $v && the $base vertex. Keep blossom bookkeeping consistent.
    function augmentBlossom($b, $v) {
        global $DEBUG;
        if ($DEBUG) {
            $DEBUG("augmentBlossom($b,$v)");
        }
        # Bubble up through the blossom tree from vertex $v to an immediate
        # sub-blossom of b.
        $t = $v;
        while ($this->blossomparent[$t] != $b) {
            $t = $this->blossomparent[$t];
        }
        # Recursively deal wi$th the first sub-blossom.
        if ($t >= $this->nvertex) {
            $this->augmentBlossom($t, $v);
        }
        # Decide in which direction we will go round the blossom.
        $i = $j = array_search($t, $this->blossomchilds[$b]);
        if ($i & 1) {
            # Start index is odd; go forward && wrap.
            $j -= count($this->blossomchilds[$b]);
            $jstep = 1;
            $endptrick = 0;
        } else {
            # Start index is even; go backward.
            $jstep = -1;
            $endptrick = 1;
        }
        # Move along the blossom until we get to the $base.
        while ($j != 0) {
            # Step to the next sub-blossom && augment it recursively.
            $j += $jstep;
            $safe_index = $this->safe_index($this->blossomchilds[$b], $j);
            $t = $this->blossomchilds[$b][$safe_index];
            $safe_index = $this->safe_index($this->blossomendps[$b], $j - $endptrick);
            $p = $this->blossomendps[$b][$safe_index] ^ $endptrick;
            if ($t >= $this->nvertex) {
                $this->augmentBlossom($t, $this->endpoint[$p]);
            }
            # Step to the next sub-blossom && augment it recursively.
            $j += $jstep;
            $safe_index = $this->safe_index($this->blossomchilds[$b], $j);
            $t = $this->blossomchilds[$b][$safe_index];
            if ($t >= $this->nvertex) {
                $this->augmentBlossom($t, $this->endpoint[$p ^ 1]);
            }
            # Match the edge connecting those sub-blossoms.
            $this->mate[$this->endpoint[$p]] = $p ^ 1;
            $this->mate[$this->endpoint[$p ^ 1]] = $p;
            if ($DEBUG) {
                $DEBUG('PAIR(a) ' . $this->endpoint[$p] . ' ' . $this->endpoint[$p ^ 1] . ' (k=' . ($this->floorintdiv($p, 2)) . ')');
            }
        }
        # Rotate the list of sub-blossoms to put the new $base at the front.
        $this->blossomchilds[$b] = array_merge(array_slice($this->blossomchilds[$b], $i), array_slice($this->blossomchilds[$b], 0, $i));
        $this->blossomendps[$b]  = array_merge(array_slice($this->blossomendps[$b], $i), array_slice($this->blossomendps[$b], 0, $i));
        $this->blossombase[$b] = $this->blossombase[$this->blossomchilds[$b][0]];
        assert($this->blossombase[$b] == $v);
    }

    # Swap matched/unmatched edges over an alternating $path between two
    # single vertices. The augmenting $path runs through edge k, which
    # connects a pair of $S vertices.
    function augmentMatching($k) {
        global $DEBUG;
        list($v, $w, $wt) = $this->edges[$k];
        if ($DEBUG) {
            $DEBUG("augmentMatching($k) (v=$v w=$w)");
        }
        if ($DEBUG) {
            $DEBUG("PAIR(b) $v $w (k=$k)");
        }
        foreach ([[$v, 2 * $k + 1], [$w, 2 * $k]] as $row) {
            list($s, $p) = $row;
            # Match vertex $s to remote $this->endpoint p. Then trace back from s
            # until we find a single vertex, swapping matched && unmatched
            # edges as we go.
            while (1) {
                $bs = $this->inblossom[$s];
                assert($this->label[$bs] == 1);
                assert($this->labelend[$bs] == $this->mate[$this->blossombase[$bs]]);
                # Augment through the S-blossom from $s to $base.
                if ($bs >= $this->nvertex) {
                    $this->augmentBlossom($bs, $s);
                }
                # Update $this->mate[$s]
                $this->mate[$s] = $p;
                # Trace one step back.
                if ($this->labelend[$bs] == -1) {
                    # Reached single vertex; stop.
                    break;
                }
                $t = $this->endpoint[$this->labelend[$bs]];
                $bt = $this->inblossom[$t];
                assert($this->label[$bt] == 2);
                # Trace one step back.
                assert($this->labelend[$bt] >= 0);
                $s = $this->endpoint[$this->labelend[$bt]];
                $j = $this->endpoint[$this->labelend[$bt] ^ 1];
                # Augment through the T-blossom from $j to $base.
                assert($this->blossombase[$bt] == $t);
                if ($bt >= $this->nvertex) {
                    $this->augmentBlossom($bt, $j);
                }
                # Update $this->mate[$j]
                $this->mate[$j] = $this->labelend[$bt];
                # Keep the opposite $this->endpoint;
                # it will be assigned to $this->mate[$s] in the next step.
                $p = $this->labelend[$bt] ^ 1;
                if ($DEBUG) {
                    $DEBUG("PAIR(c) $s $t (k=" . ($this->floorintdiv($p, 2)) . ')');
                }
            }
        }
    }

    # Verify that the optimum solution has been reached.
    function verifyOptimum() {
        global $DEBUG;
        if ($this->maxcardinality) {
            # Vertices may have negative dual;
            # find a constant non-negative number to add to all vertex duals.
            $vdualoffset = max(0, -min(array_slice($this->dualvar, 0, $this->nvertex)));
        } else {
            $vdualoffset = 0;
        }
        # 0. all dual variables are non-negative
        assert(min(array_slice($this->dualvar, 0, $this->nvertex)) + $vdualoffset >= 0);
        assert(min(array_slice($this->dualvar, $this->nvertex)) >= 0);
        # 0. all edges have non-negative slack and
        # 1. all matched edges have zero slack;
        foreach (range(0, $this->nedge - 1) as $k) {
            list($i, $j, $wt) = $this->edges[$k];
            $s = $this->dualvar[$i] + $this->dualvar[$j] - 2 * $wt;
            $iblossoms = [$i];
            $jblossoms = [$j];
            while ($this->blossomparent[$this->last_elem($iblossoms)] != -1) {
                $iblossoms[] = $this->blossomparent[$this->last_elem($iblossoms)];
            }
            while ($this->blossomparent[$this->last_elem($jblossoms)] != -1) {
                $jblossoms[] = $this->blossomparent[$this->last_elem($jblossoms)];
            }
            $iblossoms = array_reverse($iblossoms);
            $jblossoms = array_reverse($jblossoms);
            // BAKERT this replacement for zip will break if iblossoms and jblossoms are not the same length.
            foreach (array_map(null, $iblossoms, $jblossoms) as $row) {
                list($bi, $bj) = $row;
                if ($bi != $bj) {
                    break;
                }
                $s += 2 * $this->dualvar[$bi];
            }
            assert($s >= 0);
            if ($this->floorintdiv($this->mate[$i], 2) == $k || $this->floorintdiv($this->mate[$j], 2) == $k) {
                assert($this->floorintdiv($this->mate[$i], 2) == $k && $this->floorintdiv($this->mate[$j], 2) == $k);
                assert($s == 0);
            }
        }
        # 2. all single vertices have zero dual value;
        foreach (range(0, $this->nvertex - 1) as $v) {
            assert($this->mate[$v] >= 0 || $this->dualvar[$v] + $vdualoffset == 0);
        }
        # 3. all blossoms with positive dual value are full.
        foreach (range($this->nvertex, 2 * $this->nvertex - 1) as $b) {
            if ($this->blossombase[$b] >= 0 && $this->dualvar[$b] > 0) {
                assert(count($this->blossomendps[$b]) % 2 == 1);
                foreach (array_slice($this->blossomendps[$b], 1, 1) as $p) {
                    assert($this->mate[$this->endpoint[$p]] == $p ^ 1);
                    assert($this->mate[$this->endpoint[$p ^ 1]] == $p);
                }
            }
        }
        # Ok.
    }

    # Check optimized $delta2 against a trivial computation.
    function checkDelta2() {
        global $DEBUG;
        foreach (range(0, $this->nvertex - 1) as $v) {
            if ($this->label[$this->inblossom[$v]] == 0) {
                $bd = null;
                $bk = -1;
                foreach ($this->neighbend[$v] as $p) {
                    $k = $this->floorintdiv($p, 2);
                    $w = $this->endpoint[$p];
                    if ($this->label[$this->inblossom[$w]] == 1) {
                        $d = $this->slack($k);
                        if ($bk == -1 || $d < $bd) {
                            $bk = $k;
                            $bd = $d;
                        }
                    }
                }
                if ($DEBUG && ($this->bestedge[$v] != -1 || $bk != -1) && ($this->bestedge[$v] == -1 || $bd != $this->slack($this->bestedge[$v]))) {
                    $DEBUG('v=' . $v . ' bk=' . $bk . ' bd=' . $bd . ' $this->bestedge[$v]=' . $this->bestedge[$v] . ' slack=' . $this->slack($this->bestedge[$v]));
                }
                assert(($bk == -1 && $this->bestedge[$v] == -1) || ($this->bestedge[$v] != -1 && $bd == $this->slack($this->bestedge[$v])));
            }
        }
    }

    # Check optimized $delta3 against a trivial computation.
    function checkDelta3() {
        global $DEBUG;
        $bk = -1;
        $bd = null;
        $tbk = -1;
        $tbd = null;
        foreach (range(0, 2 * $this->nvertex - 1) as $b) {
            if ($this->blossomparent[$b] == -1 && $this->label[$b] == 1) {
                foreach ($this->blossomLeaves($b) as $v) {
                    foreach ($this->neighbend[$v] as $p) {
                        $k = $this->floorintdiv($p, 2);
                        $w = $this->endpoint[$p];
                        if ($this->inblossom[$w] != $b && $this->label[$this->inblossom[$w]] == 1) {
                            $d = $this->slack($k);
                            if ($bk == -1 || $d < $bd) {
                                $bk = $k;
                                $bd = $d;
                            }
                        }
                    }
                }
                if ($this->bestedge[$b] != -1) {
                    list($i, $j, $wt) = $this->edges[$this->bestedge[$b]];
                    assert($this->inblossom[$i] == $b || $this->inblossom[$j] == $b);
                    assert($this->inblossom[$i] != $b || $this->inblossom[$j] != $b);
                    assert($this->label[$this->inblossom[$i]] == 1 && $this->label[$this->inblossom[$j]] == 1);
                    if ($tbk == -1 || $this->slack($this->bestedge[$b]) < $tbd) {
                        $tbk = $this->bestedge[$b];
                        $tbd = $this->slack($this->bestedge[$b]);
                    }
                }
            }
        }
        if ($DEBUG && $bd != $tbd) {
            $DEBUG("bk=$bk tbk=$tbk bd=" .  $this->arr_repr($bd) . ' tbd=' . $this->arr_repr($tbd));
        }
        assert($bd == $tbd);
    }

    // Fake version of arr[-1] from python.
    function last_elem($arr) {
        return array_values(array_slice($arr, -1))[0];
    }

    // Fake version of x // y from python.
    function floorintdiv($x, $y) {
        return intval(floor($x / $y));
    }

    // Replace negative index with positive equivalent so we can use the logic that an index of -1 means the last element as in python.
    function safe_index($arr, $requested_index) {
        if ($requested_index >= 0) {
            return $requested_index;
        }
        return count($arr) + $requested_index;
    }

    // Allow -1 and similar to access from the end of an array instead of looking for a key of -1.
    function safe_access($arr, $requested_index) {
        return $arr[$this->safe_index($arr, $requested_index)];
    }

    // Single line array representation similar to print(arr) in python.
    function arr_repr($arr) {
        $s = '[';
        foreach ($arr as $v) {
            if (is_array($v)) {
                $s .= $this->arr_repr($v);
            } else {
                $s .= "$v, ";
            }
        }
        return rtrim($s, ', ') . ']';
    }

    function print_arr($arr) {
        print($this->arr_repr($arr) . "\n");
    }

    function main() {
        global $DEBUG;
        global $CHECK_DELTA;
        global $CHECK_OPTIMUM;

        # Deal swiftly with empty graphs.
        if (!$this->edges) {
            return [];
        }

        # Main loop: continue until no further improvement is possible.
        foreach (range(0, $this->nvertex - 1) as $t) {

            # Each iteration of this loop is a "stage".
            # A stage finds an augmenting $path && uses that to improve
            # the matching.
            if ($DEBUG) {
                $DEBUG("STAGE $t");
            }

            # Remove $this->labels from top-level blossoms/vertices.
            $this->label = array_fill(0, 2 * $this->nvertex, 0);

            # Forget all about least-slack edges.
            $this->bestedge = array_fill(0, 2 * $this->nvertex, -1);
            for ($i = $this->nvertex; $i < count($this->blossombestedges); $i++) {
                $this->blossombestedges[$i] = null;
            }

            # Loss of $this->labeling means that we can not be sure that currently
            # allowable edges remain allowable througout this stage.
            $this->allowedge = array_fill(0, $this->nedge, false);

            # Make queue empty.
            $this->queue = [];

            # $this->label single blossoms/vertices with $S && put them in the queue.
            foreach (range(0, $this->nvertex - 1) as $v) {
                if ($this->mate[$v] == -1 && $this->label[$this->inblossom[$v]] == 0) {
                    $this->assignLabel($v, 1, -1);
                }
            }

            # Loop until we succeed in augmenting the matching.
            $augmented = 0;
            while (1) {

                # Each iteration of this loop is a "substage".
                # A substage tries to find an augmenting $path;
                # if found, the $path is used to improve the matching and
                # the stage ends. If there is no augmenting $path, the
                # primal-dual method is used to pump some slack out of
                # the dual variables.
                if ($DEBUG) {
                    $DEBUG('SUBSTAGE');
                }

                # Continue $this->labeling until all vertices which are reachable
                # through an alternating $path have got a $this->label.
                while ($this->queue && !$augmented) {

                    # Take an $S vertex from the queue.
                    $v = array_pop($this->queue);
                    if ($DEBUG) {
                        $DEBUG("POP v=$v");
                    }
                    assert($this->label[$this->inblossom[$v]] == 1);

                    # Scan its neighbours:
                    foreach ($this->neighbend[$v] as $p) {
                        $k = $this->floorintdiv($p, 2);
                        $w = $this->endpoint[$p];
                        # $w is a neighbour to v
                        if ($this->inblossom[$v] == $this->inblossom[$w]) {
                            # this edge is internal to a blossom; ignore it
                            continue;
                        }
                        if (!$this->allowedge[$k]) {
                            $kslack = $this->slack($k);
                            if ($kslack <= 0) {
                                # edge k has zero slack => it is allowable
                                $this->allowedge[$k] = true;
                            }
                        }
                        if ($this->allowedge[$k]) {
                            if ($this->label[$this->inblossom[$w]] == 0) {
                                # (C1) $w is a free vertex;
                                # $this->label $w with T && $this->label its $this->mate with $S (R12).
                                $this->assignLabel($w, 2, $p ^ 1);
                            } elseif ($this->label[$this->inblossom[$w]] == 1) {
                                # (C2) $w is an S-vertex (not in the same blossom);
                                # follow back-links to discover either an
                                # augmenting $path || a new blossom.
                                $base = $this->scanBlossom($v, $w);
                                if ($base >= 0) {
                                    # Found a new blossom; add it to the blossom
                                    # bookkeeping && turn it into an S-blossom.
                                    $this->addBlossom($base, $k);
                                } else {
                                    # Found an augmenting $path; augment the
                                    # matching && end this stage.
                                    $this->augmentMatching($k);
                                    $augmented = 1;
                                    break;
                                }
                            } elseif ($this->label[$w] == 0) {
                                # $w is inside a T-blossom, but $w itself has not
                                # yet been reached from outside the blossom;
                                # mark it as reached (we need this to relabel
                                # during T-blossom expansion).
                                assert($this->label[$this->inblossom[$w]] == 2);
                                $this->label[$w] = 2;
                                $this->labelend[$w] = $p ^ 1;
                            }
                        } elseif ($this->label[$this->inblossom[$w]] == 1) {
                            # keep track of the least-slack non-allowable edge to
                            # a different S-blossom.
                            $b = $this->inblossom[$v];
                            if ($this->bestedge[$b] == -1 || $kslack < $this->slack($this->bestedge[$b])) {
                                $this->bestedge[$b] = $k;
                            }
                        } elseif ($this->label[$w] == 0) {
                            # $w is a free vertex (or an unreached vertex inside
                            # a T-blossom) but we can not reach it yet;
                            # keep track of the least-slack edge that reaches w.
                            if ($this->bestedge[$w] == -1 || $kslack < $this->slack($this->bestedge[$w])) {
                                $this->bestedge[$w] = $k;
                            }
                        }
                    }
                }

                if ($augmented) {
                    break;
                }

                # There is no augmenting $path under these constraints;
                # compute $delta && reduce slack in the optimization problem.
                # (Note that our vertex dual variables, edge slacks && $delta's
                # are pre-multiplied by two.)
                $deltatype = -1;
                $delta = $deltaedge = $deltablossom = null;

                # Verify data structures for $delta2/delta3 computation.
                if ($CHECK_DELTA) {
                    $this->checkDelta2();
                    $this->checkDelta3();
                }

                # Compute $delta1: the minumum value of any vertex dual.
                if (!$this->maxcardinality) {
                    $deltatype = 1;
                    $delta = min(array_slice($this->dualvar, 0, $this->nvertex));
                }

                # Compute $delta2: the minimum slack on any edge between
                # an S-vertex && a free vertex.
                foreach (range(0, $this->nvertex - 1) as $v) {
                    if ($this->label[$this->inblossom[$v]] == 0 && $this->bestedge[$v] != -1) {
                        $d = $this->slack($this->bestedge[$v]);
                        if ($deltatype == -1 || $d < $delta) {
                            $delta = $d;
                            $deltatype = 2;
                            $deltaedge = $this->bestedge[$v];
                        }
                    }
                }

                # Compute $delta3: half the minimum slack on any edge between
                # a pair of S-blossoms.
                foreach (range(0, 2 * $this->nvertex - 1) as $b) {
                    if ($this->blossomparent[$b] == -1 && $this->label[$b] == 1 && $this->bestedge[$b] != -1) {
                        $kslack = $this->slack($this->bestedge[$b]);
                        // BAKERT was checking for (int, long) in python version
                        if ((int)$kslack == $kslack) {
                            assert(($kslack % 2) == 0);
                            $d = $this->floorintdiv($kslack, 2);
                        } else {
                            $d = $kslack / 2; # ORIGINALLY SINGLE SLASH DIVISION IN PYTHON SO NOT CONVERTED TO INTDIV BAKERT
                        }
                        if ($deltatype == -1 || $d < $delta) {
                            $delta = $d;
                            $deltatype = 3;
                            $deltaedge = $this->bestedge[$b];
                        }
                    }
                }

                # Compute $delta4: minimum z variable of any T-blossom.
                foreach (range($this->nvertex, 2 * $this->nvertex - 1) as $b) {
                    if ($this->blossombase[$b] >= 0 && $this->blossomparent[$b] == -1 &&
                            $this->label[$b] == 2 &&
                            ($deltatype == -1 || $this->dualvar[$b] < $delta)) {
                        $delta = $this->dualvar[$b];
                        $deltatype = 4;
                        $deltablossom = $b;
                    }
                }

                if ($deltatype == -1) {
                    # No further improvement possible; max-cardinality optimum
                    # reached. Do a final $delta update to make the optimum
                    # verifyable.
                    assert($this->maxcardinality);
                    $deltatype = 1;
                    $delta = max(0, min(array_slice($this->dualvar, 0, $this->nvertex)));
                }

                # Update dual variables according to $delta.
                foreach (range(0, $this->nvertex - 1) as $v) {
                    if ($this->label[$this->inblossom[$v]] == 1) {
                        # S-vertex: 2*u = 2*u - 2*delta
                        $this->dualvar[$v] -= $delta;
                    } elseif ($this->label[$this->inblossom[$v]] == 2) {
                        # T-vertex: 2*u = 2*u + 2*del
                        $this->dualvar[$v] += $delta;
                    }
                }
                foreach (range($this->nvertex, 2 * $this->nvertex - 1) as $b) {
                    if ($this->blossombase[$b] >= 0 && $this->blossomparent[$b] == -1) {
                        if ($this->label[$b] == 1) {
                            # top-level S-blossom: z = z + 2*delta
                            $this->dualvar[$b] += $delta;
                        } elseif ($this->label[$b] == 2) {
                            # top-level T-blossom: z = z - 2*delta
                            $this->dualvar[$b] -= $delta;
                        }
                    }
                }

                # Take action at the point where minimum $delta occurred.
                if ($DEBUG) {
                    $DEBUG("delta$deltatype=$delta");
                }
                if ($deltatype == 1) {
                    # No further improvement possible; optimum reached.
                    break;
                } elseif ($deltatype == 2) {
                    # Use the least-slack edge to continue the search.
                    $this->allowedge[$deltaedge] = true;
                    list($i, $j, $wt) = $this->edges[$deltaedge];
                    if ($this->label[$this->inblossom[$i]] == 0) {
                        list($i, $j) = [$j, $i];
                    }
                    assert($this->label[$this->inblossom[$i]] == 1);
                    $this->queue[] = $i;
                } elseif ($deltatype == 3) {
                    # Use the least-slack edge to continue the search.
                    $this->allowedge[$deltaedge] = true;
                    list($i, $j, $wt) = $this->edges[$deltaedge];
                    assert($this->label[$this->inblossom[$i]] == 1);
                    $this->queue[] = $i;
                } elseif ($deltatype == 4) {
                    # Expand the least-z blossom.
                    $this->expandBlossom($deltablossom, false);
                }

                # End of a this substage.
            }


            # Stop when no more augmenting $path can be found.
            if (!$augmented) {
                break;
            }

            # End of a stage; expand all S-blossoms which have $this->dualvar = 0.
            foreach (range($this->nvertex, 2 * $this->nvertex - 1) as $b) {
                if ($this->blossomparent[$b] == -1 && $this->blossombase[$b] >= 0 &&
                        $this->label[$b] == 1 && $this->dualvar[$b] == 0) {
                    $this->expandBlossom($b, true);
                }
            }
        }

        # Verify that we reached the optimum solution.
        if ($CHECK_OPTIMUM) {
            $this->verifyOptimum();
        }

        # Transform $this->mate[] such that $this->mate[$v] is the vertex to which $v is paired.
        foreach (range(0, $this->nvertex - 1) as $v) {
            if ($this->mate[$v] >= 0) {
                $this->mate[$v] = $this->endpoint[$this->mate[$v]];
            }
        }
        foreach (range(0, $this->nvertex - 1) as $v) {
            assert($this->mate[$v] == -1 || $this->mate[$this->mate[$v]] == $v);
        }

        return $this->mate;
    }
}

# Unit tests
if (isset($argv) && $argv && $argv[0] && realpath($argv[0]) === __FILE__) {

    class MaxWeightMatchingTests {

        function test10_empty() {
            # empty input graph
            assert(maxWeightMatching([]) === []);
        }

        function test11_singleedge() {
            # single edge
            assert(maxWeightMatching([[0, 1, 1]]), [1, 0]);
        }

       function test12() {
            assert(maxWeightMatching([[1, 2, 10], [2, 3, 11]]), [-1, -1, 3, 2]);
        }

        function test13() {
            assert(maxWeightMatching([[1, 2, 5], [2, 3, 11], [3, 4, 5]]), [-1, -1, 3, 2, -1]);
        }


        function test14_maxcard() {
            # maximum cardinality
            assert(maxWeightMatching([[1, 2, 5], [2, 3, 11], [3, 4, 5]], true), [-1, 2, 1, 4, 3]);
        }

        function test15_float() {
            # floating point weigths
            assert(maxWeightMatching([[1, 2, M_PI], [2, 3, exp(1)], [1, 3, 3.0], [1, 4, sqrt(2.0)]]), [-1, 4, 3, 2, 1]);
        }

        function test16_negative() {
            # negative weights
            assert(maxWeightMatching([[1, 2, 2], [1, 3, -2], [2, 3, 1], [2, 4, -1], [3, 4, -6]], false), [-1, 2, 1, -1, -1]);
            assert(maxWeightMatching([[1, 2, 2], [1, 3, -2], [2, 3, 1], [2, 4, -1], [3, 4, -6]], true), [-1, 3, 4, 1, 2]);
        }


        function test20_sblossom() {
            # create S-blossom && use it for augmentation
            assert(maxWeightMatching([[1, 2, 8], [1, 3, 9], [2, 3, 10], [3, 4, 7]]), [-1, 2, 1, 4, 3]);
            assert(maxWeightMatching([[1, 2, 8], [1, 3, 9], [2, 3, 10], [3, 4, 7], [1, 6, 5], [4, 5, 6]]), [-1, 6, 3, 2, 5, 4, 1]);
        }

        function test21_tblossom() {
            # create S-blossom, relabel as T-blossom, use for augmentation
            assert(maxWeightMatching([[1, 2, 9], [1, 3, 8], [2, 3, 10], [1, 4, 5], [4, 5, 4], [1, 6, 3]]), [-1, 6, 3, 2, 5, 4, 1]);
            assert(maxWeightMatching([[1, 2, 9], [1, 3, 8], [2, 3, 10], [1, 4, 5], [4, 5, 3], [1, 6, 4]]), [-1, 6, 3, 2, 5, 4, 1]);
            assert(maxWeightMatching([[1, 2, 9], [1, 3, 8], [2, 3, 10], [1, 4, 5], [4, 5, 3], [3, 6, 4]]), [-1, 2, 1, 6, 5, 4, 3]);
        }

        function test22_s_nest() {
            # create nested S-blossom, use for augmentation
            assert(maxWeightMatching([[1, 2, 9], [1, 3, 9], [2, 3, 10], [2, 4, 8], [3, 5, 8], [4, 5, 10], [5, 6, 6]]), [-1, 3, 4, 1, 2, 6, 5]);
        }

        function test23_s_relabel_nest() {
            # create S-blossom, relabel as S, include in nested S-blossom
            assert(maxWeightMatching([[1, 2, 10], [1, 7, 10], [2, 3, 12], [3, 4, 20], [3, 5, 20], [4, 5, 25], [5, 6, 10], [6, 7, 10], [7, 8, 8]]), [-1, 2, 1, 4, 3, 6, 5, 8, 7]);
        }

        function test24_s_nest_expand() {
            # create nested S-blossom, augment, expand recursively
            assert(maxWeightMatching([[1, 2, 8], [1, 3, 8], [2, 3, 10], [2, 4, 12], [3, 5, 12], [4, 5, 14], [4, 6, 12], [5, 7, 12], [6, 7, 14], [7, 8, 12]]), [-1, 2, 1, 5, 6, 3, 4, 8, 7]);
        }

        function test25_s_t_expand() {
            # create S-blossom, relabel as T, expand
            assert(maxWeightMatching([[1, 2, 23], [1, 5, 22], [1, 6, 15], [2, 3, 25], [3, 4, 22], [4, 5, 25], [4, 8, 14], [5, 7, 13]]), [-1, 6, 3, 2, 8, 7, 1, 5, 4]);
        }

        function test26_s_nest_t_expand() {
            # create nested S-blossom, relabel as T, expand
            assert(maxWeightMatching([[1, 2, 19], [1, 3, 20], [1, 8, 8], [2, 3, 25], [2, 4, 18], [3, 5, 18], [4, 5, 13], [4, 7, 7], [5, 6, 7]]), [-1, 8, 3, 2, 7, 6, 5, 4, 1]);
        }

        function test30_tnasty_expand() {
            # create blossom, relabel as T in more than one way, expand, augment
            assert(maxWeightMatching([[1, 2, 45], [1, 5, 45], [2, 3, 50], [3, 4, 45], [4, 5, 50], [1, 6, 30], [3, 9, 35], [4, 8, 35], [5, 7, 26], [9, 10, 5]]), [-1, 6, 3, 2, 8, 7, 1, 5, 4, 10, 9]);
        }

        function test31_tnasty2_expand() {
            # again but slightly different
            assert(maxWeightMatching([[1, 2, 45], [1, 5, 45], [2, 3, 50], [3, 4, 45], [4, 5, 50], [1, 6, 30], [3, 9, 35], [4, 8, 26], [5, 7, 40], [9, 10, 5]]), [-1, 6, 3, 2, 8, 7, 1, 5, 4, 10, 9]);
        }

        function test32_t_expand_leastslack() {
            # create blossom, relabel as T, expand such that a new least-slack S-to-free edge is produced, augment
            assert(maxWeightMatching([[1, 2, 45], [1, 5, 45], [2, 3, 50], [3, 4, 45], [4, 5, 50], [1, 6, 30], [3, 9, 35], [4, 8, 28], [5, 7, 26], [9, 10, 5]]), [-1, 6, 3, 2, 8, 7, 1, 5, 4, 10, 9]);
        }

        function test33_nest_tnasty_expand() {
            # create nested blossom, relabel as T in more than one way, expand outer blossom such that inner blossom ends up on an augmenting $path
            assert(maxWeightMatching([[1, 2, 45], [1, 7, 45], [2, 3, 50], [3, 4, 45], [4, 5, 95], [4, 6, 94], [5, 6, 94], [6, 7, 50], [1, 8, 30], [3, 11, 35], [5, 9, 36], [7, 10, 26], [11, 12, 5]]), [-1, 8, 3, 2, 6, 9, 4, 10, 1, 5, 7, 12, 11]);
        }

        function test34_nest_relabel_expand() {
            # create nested S-blossom, relabel as S, expand recursively
            assert(maxWeightMatching([[1, 2, 40], [1, 3, 40], [2, 3, 60], [2, 4, 55], [3, 5, 55], [4, 5, 50], [1, 8, 15], [5, 7, 30], [7, 6, 10], [8, 10, 10], [4, 9, 30]]), [-1, 2, 1, 5, 9, 3, 7, 6, 10, 4, 8]);
        }
    }

    function test() {
        global $DEBUG;
        $unit_tests = new MaxWeightMatchingTests();
        $method_names = preg_grep('/^test/', get_class_methods($unit_tests));
        foreach ($method_names as $name) {
            $unit_tests->$name();
            print('.');
        }
        print("\n");
    }

    $CHECK_DELTA = true;
    test();
}
