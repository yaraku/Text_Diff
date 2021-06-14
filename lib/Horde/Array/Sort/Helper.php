<?php

namespace Horde\Array\Sort;

use Horde\HordeString;

/**
 * Helper class for sorting arrays on arbitrary criteria for usort/uasort.
 *
 * Copyright 2003-2013 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @author   Marko Djukic <marko@oblo.com>
 * @author   Jan Schneider <jan@horde.org>
 * @author   Michael Slusarz <slusarz@horde.org>
 * @category Horde
 * @license  http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package  Util
 */
class Helper
{
    /**
     * The array key to sort by.
     *
     * @var string
     */
    public $key;

    /**
     * Compare two associative arrays by the array key defined in self::$key.
     *
     * @param array $a
     * @param array $b
     */
    public function compare($a, $b): int
    {
        return strcoll(HordeString::lower($a[$this->key], true, 'UTF-8'), HordeString::lower($b[$this->key], true, 'UTF-8'));
    }

    /**
     * Compare, in reverse order, two associative arrays by the array key
     * defined in self::$key.
     *
     * @param scalar $a  TODO
     * @param scalar $b  TODO
     */
    public function reverseCompare($a, $b): int
    {
        return strcoll(HordeString::lower($b[$this->key], true, 'UTF-8'), HordeString::lower($a[$this->key], true, 'UTF-8'));
    }

    /**
     * Compare array keys case insensitively for uksort.
     *
     * @param scalar $a  TODO
     * @param scalar $b  TODO
     */
    public function compareKeys($a, $b): int
    {
        return strcoll(HordeString::lower($a, true, 'UTF-8'), HordeString::lower($b, true, 'UTF-8'));
    }

    /**
     * Compare, in reverse order, array keys case insensitively for uksort.
     *
     * @param scalar $a  TODO
     * @param scalar $b  TODO
     */
    public function reverseCompareKeys($a, $b): int
    {
        return strcoll(HordeString::lower($b, true, 'UTF-8'), HordeString::lower($a, true, 'UTF-8'));
    }
}
