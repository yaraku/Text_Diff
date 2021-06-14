<?php

namespace Horde\Exception;

use Horde\HordeTranslation;

/**
 * @package Exception
 *
 * Copyright 2010-2013 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 */

/**
 * Horde_Exception_Translation is the translation wrapper class for HordeException.
 *
 * @author  Jan Schneider <jan@horde.org>
 * @package Exception
 */
class TranslationException extends HordeTranslation
{
    /**
     * Returns the translation of a message.
     *
     * @var string $message  The string to translate.
     *
     * @return string  The string translation, or the original string if no
     *                 translation exists.
     */
    public static function t($message)
    {
        self::$_domain = 'HordeException';
        self::$_directory = '/app/vendor/pear-pear.horde.org/Horde_Exception/data' == '@'.'data_dir'.'@' ? __DIR__ . '/../../../locale' : '/app/vendor/pear-pear.horde.org/Horde_Exception/data/Horde_Exception/locale';
        return parent::t($message);
    }

    /**
     * Returns the plural translation of a message.
     *
     * @param string $singular  The singular version to translate.
     * @param string $plural    The plural version to translate.
     * @param int $number   The number that determines singular vs. plural.
     *
     * @return string  The string translation, or the original string if no
     *                 translation exists.
     */
    public static function ngettext($singular, $plural, $number)
    {
        self::$_domain = 'Horde_Exception';
        self::$_directory = '/app/vendor/pear-pear.horde.org/Horde_Exception/data' == '@'.'data_dir'.'@' ? __DIR__ . '/../../../locale' : '/app/vendor/pear-pear.horde.org/Horde_Exception/data/Horde_Exception/locale';
        return parent::ngettext($singular, $plural, $number);
    }
}
