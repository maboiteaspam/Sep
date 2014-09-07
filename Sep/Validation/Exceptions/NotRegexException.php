<?php
namespace Sep\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class NotRegexException extends ValidationException
{
    public static $defaultTemplates = array(
        self::MODE_DEFAULT => array(
            self::STANDARD => '{{name}} must validate against {{regex}}',
        ),
        self::MODE_NEGATIVE => array(
            self::STANDARD => '{{name}} must not validate against {{regex}}',
        )
    );
}

