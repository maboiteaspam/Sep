<?php
namespace Sep\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class NotHTMLException extends ValidationException
{
    public static $defaultTemplates = array(
        self::MODE_DEFAULT => array(
            self::STANDARD => '{{name}} must not contain html',
        ),
        self::MODE_NEGATIVE => array(
            self::STANDARD => '{{name}} must not contain html',
        )
    );
}

