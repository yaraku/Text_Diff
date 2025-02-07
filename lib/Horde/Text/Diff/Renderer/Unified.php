<?php

namespace Horde\Text\Diff\Renderer;

use Horde\Text\Diff\Renderer;

/**
 * "Unified" diff renderer.
 *
 * This class renders the diff in classic "unified diff" format.
 *
 * Copyright 2004-2017 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you did
 * not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @author  Ciprian Popovici
 * @package Text_Diff
 */
class Unified extends Renderer
{
    /**
     * Number of leading context "lines" to preserve.
     */
    protected int $_leading_context_lines = 4;

    /**
     * Number of trailing context "lines" to preserve.
     */
    protected int $_trailing_context_lines = 4;

    protected function _blockHeader($xbeg, $xlen, $ybeg, $ylen): string
    {
        if ($xlen != 1) {
            $xbeg .= ',' . $xlen;
        }
        if ($ylen != 1) {
            $ybeg .= ',' . $ylen;
        }
        return "@@ -$xbeg +$ybeg @@";
    }

    protected function _context($lines): string
    {
        return $this->_lines($lines);
    }

    protected function _added($lines): string
    {
        return $this->_lines($lines, '+');
    }

    protected function _deleted($lines): string
    {
        return $this->_lines($lines, '-');
    }

    protected function _changed($orig, $final): string
    {
        return $this->_deleted($orig) . $this->_added($final);
    }
}
