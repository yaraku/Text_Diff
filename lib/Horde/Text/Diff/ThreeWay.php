<?php

namespace Horde\Text\Diff;

use Horde\Text\Diff\Engine\NativeEngine;
use Horde\Text\Diff\Engine\XdiffEngine;
use Horde\Text\Diff\ThreeWay\BlockBuilder;

/**
 * A class for computing three way merges.
 *
 * Copyright 2007-2017 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you did
 * not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @package Text_Diff
 * @author  Geoffrey T. Dairiki <dairiki@dairiki.org>
 */
class ThreeWay
{
    /**
     * Array of changes.
     *
     * @var array
     */
    protected array $_edits;

    /**
     * Conflict counter.
     *
     * @var int
     */
    protected int $_conflictingBlocks = 0;

    /**
     * Computes diff between 3 sequences of strings.
     *
     * @param array $orig The original lines to use.
     * @param array $final1 The first version to compare to.
     * @param array $final2 The second version to compare to.
     * @throws DiffException
     */
    public function __construct(array $orig, array $final1, array $final2)
    {
        if (extension_loaded('xdiff')) {
            $engine = new XdiffEngine();
        } else {
            $engine = new NativeEngine();
        }

        $this->_edits = $this->_diff3(
            $engine->diff($orig, $final1),
            $engine->diff($orig, $final2)
        );
    }

    /**
     */
    public function mergedOutput($label1 = false, $label2 = false): array
    {
        $lines = [];
        foreach ($this->_edits as $edit) {
            if ($edit->isConflict()) {
                /* FIXME: this should probably be moved somewhere else. */
                $lines = array_merge(
                    $lines,
                    ['<<<<<<<' . ($label1 ? ' ' . $label1 : '')],
                    $edit->final1,
                    ["======="],
                    $edit->final2,
                    ['>>>>>>>' . ($label2 ? ' ' . $label2 : '')]
                );
                $this->_conflictingBlocks++;
            } else {
                $lines = array_merge($lines, $edit->merged());
            }
        }

        return $lines;
    }

    /**
     */
    protected function _diff3($edits1, $edits2): array
    {
        $edits = [];
        $bb = new BlockBuilder();

        $e1 = current($edits1);
        $e2 = current($edits2);
        while ($e1 || $e2) {
            if ($e1 && $e2 &&
                $e1 instanceof Op\Copy &&
                $e2 instanceof Op\Copy) {
                /* We have copy blocks from both diffs. This is the (only)
                 * time we want to emit a diff3 copy block.  Flush current
                 * diff3 diff block, if any. */
                if ($edit = $bb->finish()) {
                    $edits[] = $edit;
                }

                $ncopy = min($e1->norig(), $e2->norig());
                assert($ncopy > 0);
                $edits[] = new ThreeWay\Op\Copy(array_slice($e1->orig, 0, $ncopy));

                if ($e1->norig() > $ncopy) {
                    array_splice($e1->orig, 0, $ncopy);
                    array_splice($e1->final, 0, $ncopy);
                } else {
                    $e1 = next($edits1);
                }

                if ($e2->norig() > $ncopy) {
                    array_splice($e2->orig, 0, $ncopy);
                    array_splice($e2->final, 0, $ncopy);
                } else {
                    $e2 = next($edits2);
                }
            } else {
                if ($e1 && $e2) {
                    if ($e1->orig && $e2->orig) {
                        $norig = min($e1->norig(), $e2->norig());
                        $orig = array_splice($e1->orig, 0, $norig);
                        array_splice($e2->orig, 0, $norig);
                        $bb->input($orig);
                    }

                    if ($e1 instanceof Op\Copy) {
                        $bb->out1(array_splice($e1->final, 0, $norig));
                    }

                    if ($e2 instanceof Op\Copy) {
                        $bb->out2(array_splice($e2->final, 0, $norig));
                    }
                }

                if ($e1 && ! $e1->orig) {
                    $bb->out1($e1->final);
                    $e1 = next($edits1);
                }
                if ($e2 && ! $e2->orig) {
                    $bb->out2($e2->final);
                    $e2 = next($edits2);
                }
            }
        }

        if ($edit = $bb->finish()) {
            $edits[] = $edit;
        }

        return $edits;
    }
}
