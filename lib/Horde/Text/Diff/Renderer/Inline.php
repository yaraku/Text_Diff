<?php

namespace Horde\Text\Diff\Renderer;

use Horde\Text\Diff;
use Horde\Text\HordeDiff;

/**
 * "Inline" diff renderer.
 *
 * This class renders diffs in the Wiki-style "inline" format.
 *
 * Copyright 2004-2017 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you did
 * not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @author  Ciprian Popovici
 * @package Text_Diff
 */
class Inline extends Diff\Renderer
{
    /**
     * Number of leading context "lines" to preserve.
     *
     * @var int
     */
    protected int $_leading_context_lines = 10000;

    /**
     * Number of trailing context "lines" to preserve.
     *
     * @var int
     */
    protected int $_trailing_context_lines = 10000;

    /**
     * Prefix for inserted text.
     *
     * @var string
     */
    protected string $_ins_prefix = '<ins>';

    /**
     * Suffix for inserted text.
     *
     * @var string
     */
    protected string $_ins_suffix = '</ins>';

    /**
     * Prefix for deleted text.
     *
     * @var string
     */
    protected string $_del_prefix = '<del>';

    /**
     * Suffix for deleted text.
     *
     * @var string
     */
    protected string $_del_suffix = '</del>';

    /**
     * Header for each change block.
     *
     * @var string
     */
    protected string $_block_header = '';

    /**
     * Whether to split down to character-level.
     *
     * @var bool
     */
    protected bool $_split_characters = false;

    /**
     * What are we currently splitting on? Used to recurse to show word-level
     * or character-level changes.
     *
     * @var string
     */
    protected string $_split_level = 'lines';

    protected function _blockHeader($xbeg, $xlen, $ybeg, $ylen): string
    {
        return $this->_block_header;
    }

    protected function _startBlock($header): string
    {
        return $header;
    }

    protected function _lines($lines, $prefix = ' ', $encode = true): string
    {
        if ($encode) {
            array_walk($lines, [&$this, '_encode']);
        }

        if ($this->_split_level === 'lines') {
            return implode("\n", $lines) . "\n";
        } else {
            return implode('', $lines);
        }
    }

    protected function _added($lines): string
    {
        array_walk($lines, [&$this, '_encode']);
        $lines[0] = $this->_ins_prefix . $lines[0];
        $lines[count($lines) - 1] .= $this->_ins_suffix;
        return $this->_lines($lines, ' ', false);
    }

    protected function _deleted($lines, $words = false): string
    {
        array_walk($lines, [&$this, '_encode']);
        $lines[0] = $this->_del_prefix . $lines[0];
        $lines[count($lines) - 1] .= $this->_del_suffix;
        return $this->_lines($lines, ' ', false);
    }

    protected function _changed($orig, $final): string
    {
        /* If we've already split on characters, just display. */
        if ($this->_split_level === 'characters') {
            return $this->_deleted($orig)
                . $this->_added($final);
        }

        /* If we've already split on words, just display. */
        if ($this->_split_level === 'words') {
            $prefix = '';
            while ($orig[0] !== false && $final[0] !== false &&
                str_starts_with($orig[0], ' ') &&
                str_starts_with($final[0], ' ')) {
                $prefix .= substr($orig[0], 0, 1);
                $orig[0] = substr($orig[0], 1);
                $final[0] = substr($final[0], 1);
            }
            return $prefix . $this->_deleted($orig) . $this->_added($final);
        }

        $text1 = implode("\n", $orig);
        $text2 = implode("\n", $final);

        /* Non-printing newline marker. */
        $nl = "\0";

        if ($this->_split_characters) {
            $diff = new HordeDiff(
                'native',
                [preg_split('//u', str_replace("\n", $nl, $text1)),
                                        preg_split('//u', str_replace("\n", $nl, $text2))]
            );
        } else {
            /* We want to split on word boundaries, but we need to preserve
             * whitespace as well. Therefore we split on words, but include
             * all blocks of whitespace in the wordlist. */
            $diff = new HordeDiff(
                'native',
                [$this->_splitOnWords($text1, $nl),
                                        $this->_splitOnWords($text2, $nl)]
            );
        }

        /* Get the diff in inline format. */
        $renderer = new self(array_merge(
                $this->getParams(),
                ['split_level' => $this->_split_characters ? 'characters' : 'words']
            ));

        /* Run the diff and get the output. */
        return str_replace($nl, "\n", $renderer->render($diff)) . "\n";
    }

    protected function _splitOnWords($string, $newlineEscape = "\n"): array
    {
        // Ignore \0; otherwise the while loop will never finish.
        $string = str_replace("\0", '', $string);

        $words = [];
        $length = strlen($string);
        $pos = 0;

        while ($pos < $length) {
            // Eat a word with any preceding whitespace.
            $spaces = strspn(substr($string, $pos), " \n");
            $nextpos = strcspn(substr($string, $pos + $spaces), " \n");
            $words[] = str_replace("\n", $newlineEscape, substr($string, $pos, $spaces + $nextpos));
            $pos += $spaces + $nextpos;
        }

        return $words;
    }

    protected function _encode(&$string): void
    {
        $string = htmlspecialchars($string);
    }
}
