<?php

namespace Horde\Text\Diff\Op;

/**
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
abstract class Base
{
    public mixed $orig;
    public mixed $final;

    abstract public function reverse();

    public function norig()
    {
        return $this->orig ? count($this->orig) : 0;
    }

    public function nfinal()
    {
        return $this->final ? count($this->final) : 0;
    }
}
