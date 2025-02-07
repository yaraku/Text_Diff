<?php

namespace Horde\Text\Diff\Engine;

use Horde\Text\Diff\DiffException;
use Horde\Text\Diff\Op;

/**
 * Class used internally by Diff to actually compute the diffs.
 *
 * This class uses the xdiff PECL package (http://pecl.php.net/package/xdiff)
 * to compute the differences between the two input arrays.
 *
 * Copyright 2004-2017 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you did
 * not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @author  Jon Parise <jon@horde.org>
 * @package Text_Diff
 */
class XdiffEngine
{
    /**
     * @throws DiffException
     */
    public function diff($from_lines, $to_lines): array
    {
        if (!extension_loaded('xdiff')) {
            throw new DiffException('The xdiff extension is required for this diff engine');
        }

        array_walk($from_lines, ['Horde\Text\HordeDiff', 'trimNewlines']);
        array_walk($to_lines, ['Horde\Text\HordeDiff', 'trimNewlines']);

        /* Convert the two input arrays into strings for xdiff processing. */
        $from_string = implode("\n", $from_lines);
        $to_string = implode("\n", $to_lines);

        /* Diff the two strings and convert the result to an array. */
        $diff = xdiff_string_diff($from_string, $to_string, count($to_lines));
        $diff = explode("\n", $diff);

        /* Walk through the diff one line at a time.  We build the $edits
         * array of diff operations by reading the first character of the
         * xdiff output (which is in the "unified diff" format).
         *
         * Note that we don't have enough information to detect "changed"
         * lines using this approach, so we can't add Horde_Text_Diff_Op_Changed
         * instances to the $edits array.  The result is still perfectly
         * valid, albeit a little less descriptive and efficient. */
        $edits = [];
        foreach ($diff as $line) {
            if (!strlen($line)) {
                continue;
            }
            switch ($line[0]) {
            case ' ':
                $edits[] = new Op\Copy([substr($line, 1)]);
                break;

            case '+':
                $edits[] = new Op\Add([substr($line, 1)]);
                break;

            case '-':
                $edits[] = new Op\Delete([substr($line, 1)]);
                break;
            }
        }

        return $edits;
    }
}
