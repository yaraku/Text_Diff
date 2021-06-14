<?php

namespace Horde\Exception;

use Horde\HordeException;

/**
 * Exception thrown if an object wasn't found.
 *
 * Copyright 2010-2013 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @category Horde
 * @package  Exception
 */
class NotFoundException extends HordeException
{
    /**
     * Constructor.
     *
     * @see TranslationException::__construct()
     *
     * @param mixed $message           The exception message, a PEAR_Error
     *                                 object, or an Exception object.
     * @param int $code            A numeric error code.
     */
    public function __construct($message = null, $code = null)
    {
        if (is_null($message)) {
            $message = TranslationException::t("Not Found");
        }
        parent::__construct($message, $code);
    }
}
