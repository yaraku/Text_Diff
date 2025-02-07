<?php

namespace Horde\Text;

use Horde\HordeString;
use Horde\Text\Diff\Op;

/**
 * General API for generating and formatting diffs - the differences between
 * two sequences of strings.
 *
 * The original PHP version of this code was written by Geoffrey T. Dairiki
 * <dairiki@dairiki.org>, and is used/adapted with his permission.
 *
 * Copyright 2004 Geoffrey T. Dairiki <dairiki@dairiki.org>
 * Copyright 2004-2017 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you did
 * not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @package Text_Diff
 * @author  Geoffrey T. Dairiki <dairiki@dairiki.org>
 */
class HordeDiff
{
    /**
     * Array of changes.
     *
     * @var array
     */
    protected mixed $_edits;

    /**
     * Computes diffs between sequences of strings.
     *
     * @param string $engine Name of the diffing engine to use.  'auto'
     *                           will automatically select the best.
     * @param array $params Parameters to pass to the diffing engine.
     *                           Normally an array of two arrays, each
     *                           containing the lines from a file.
     */
    public function __construct(string $engine, array $params)
    {
        if ($engine === 'auto') {
            $engine = extension_loaded('xdiff') ? 'Xdiff' : 'Native';
        } else {
            $engine = HordeString::ucfirst(basename($engine));
        }
        $class = 'Horde\\Text\\Diff\\Engine\\' . $engine . 'Engine';
        $diff_engine = new $class();

        $this->_edits = call_user_func_array([$diff_engine, 'diff'], $params);
    }

    /**
     * Returns the array of differences.
     */
    public function getDiff()
    {
        return $this->_edits;
    }

    /**
     * returns the number of new (added) lines in a given diff.
     *
     * @return int The number of new lines
     */
    public function countAddedLines(): int
    {
        $count = 0;
        foreach ($this->_edits as $edit) {
            if ($edit instanceof Op\Add ||
                $edit instanceof Op\Change) {
                $count += $edit->nfinal();
            }
        }
        return $count;
    }

    /**
     * Returns the number of deleted (removed) lines in a given diff.
     *
     * @return int The number of deleted lines
     */
    public function countDeletedLines(): int
    {
        $count = 0;
        foreach ($this->_edits as $edit) {
            if ($edit instanceof Op\Delete ||
                $edit instanceof Op\Change) {
                $count += $edit->norig();
            }
        }
        return $count;
    }

    /**
     * Computes a reversed diff.
     *
     * Example:
     * <code>
     * $diff = new Horde_Text_Diff($lines1, $lines2);
     * $rev = $diff->reverse();
     * </code>
     *
     * @return HordeDiff  A Diff object representing the inverse of the
     *                    original diff.  Note that we purposely don't return a
     *                    reference here, since this essentially is a clone()
     *                    method.
     */
    public function reverse(): HordeDiff
    {
        if (version_compare(zend_version(), '2', '>')) {
            $rev = clone($this);
        } else {
            $rev = $this;
        }
        $rev->_edits = [];
        foreach ($this->_edits as $edit) {
            $rev->_edits[] = $edit->reverse();
        }
        return $rev;
    }

    /**
     * Checks for an empty diff.
     *
     * @return bool  True if two sequences were identical.
     */
    public function isEmpty(): bool
    {
        foreach ($this->_edits as $edit) {
            if (!($edit instanceof Op\Copy)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Computes the length of the Longest Common Subsequence (LCS).
     *
     * This is mostly for diagnostic purposes.
     *
     * @return int  The length of the LCS.
     */
    public function lcs(): int
    {
        $lcs = 0;
        foreach ($this->_edits as $edit) {
            if ($edit instanceof Op\Copy) {
                $lcs += count($edit->orig);
            }
        }
        return $lcs;
    }

    /**
     * Gets the original set of lines.
     *
     * This reconstructs the $from_lines parameter passed to the constructor.
     *
     * @return array  The original sequence of strings.
     */
    public function getOriginal(): array
    {
        $lines = [];
        foreach ($this->_edits as $edit) {
            if ($edit->orig) {
                array_splice($lines, count($lines), 0, $edit->orig);
            }
        }
        return $lines;
    }

    /**
     * Gets the final set of lines.
     *
     * This reconstructs the $to_lines parameter passed to the constructor.
     *
     * @return array  The sequence of strings.
     */
    public function getFinal(): array
    {
        $lines = [];
        foreach ($this->_edits as $edit) {
            if ($edit->final) {
                array_splice($lines, count($lines), 0, $edit->final);
            }
        }
        return $lines;
    }

    /**
     * Removes trailing newlines from a line of text. This is meant to be used
     * with array_walk().
     *
     * @param string $line The line to trim.
     * @param int $key The index of the line in the array. Not used.
     */
    public static function trimNewlines(string &$line, int $key): void
    {
        $line = str_replace(["\n", "\r"], '', $line);
    }

    /**
     * Checks a diff for validity.
     *
     * This is here only for debugging purposes.
     */
    protected function _check($from_lines, $to_lines): bool
    {
        if (serialize($from_lines) !== serialize($this->getOriginal())) {
            trigger_error("Reconstructed original doesn't match", E_USER_ERROR);
        }
        if (serialize($to_lines) !== serialize($this->getFinal())) {
            trigger_error("Reconstructed final doesn't match", E_USER_ERROR);
        }

        $rev = $this->reverse();
        if (serialize($to_lines) !== serialize($rev->getOriginal())) {
            trigger_error("Reversed original doesn't match", E_USER_ERROR);
        }
        if (serialize($from_lines) !== serialize($rev->getFinal())) {
            trigger_error("Reversed final doesn't match", E_USER_ERROR);
        }

        $prevtype = null;
        foreach ($this->_edits as $edit) {
            if ($prevtype === get_class($edit)) {
                trigger_error("Edit sequence is non-optimal", E_USER_ERROR);
            }
            $prevtype = get_class($edit);
        }

        return true;
    }
}
