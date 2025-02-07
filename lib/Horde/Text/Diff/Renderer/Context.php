<?php

namespace Horde\Text\Diff\Renderer;

use Horde\Text\Diff\Renderer;

/**
 * "Context" diff renderer.
 *
 * This class renders the diff in classic "context diff" format.
 *
 * Copyright 2004-2017 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you did
 * not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @package Text_Diff
 */
class Context extends Renderer
{
    /**
     * Number of leading context "lines" to preserve.
     */
    protected int $_leading_context_lines = 4;

    /**
     * Number of trailing context "lines" to preserve.
     */
    protected int $_trailing_context_lines = 4;

    /**
     * @var string
     */
    protected string $_second_block = '';

    protected function _blockHeader($xbeg, $xlen, $ybeg, $ylen): string
    {
        if ($xlen != 1) {
            $xbeg .= ',' . $xlen;
        }
        if ($ylen != 1) {
            $ybeg .= ',' . $ylen;
        }
        $this->_second_block = "--- $ybeg ----\n";
        return "***************\n*** $xbeg ****";
    }

    protected function _endBlock(): string
    {
        return $this->_second_block;
    }

    protected function _context($lines): string
    {
        $this->_second_block .= $this->_lines($lines, '  ');
        return $this->_lines($lines, '  ');
    }

    protected function _added($lines): string
    {
        $this->_second_block .= $this->_lines($lines, '+ ');
        return '';
    }

    protected function _deleted($lines): string
    {
        return $this->_lines($lines, '- ');
    }

    protected function _changed($orig, $final): string
    {
        $this->_second_block .= $this->_lines($final, '! ');
        return $this->_lines($orig, '! ');
    }
}
