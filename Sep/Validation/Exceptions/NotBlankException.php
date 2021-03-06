<?php
namespace Sep\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class NotBlankException extends ValidationException
{
    const STANDARD = 0;
    const NAMED = 1;
    public static $defaultTemplates = array(
        self::MODE_DEFAULT => array(
            self::STANDARD => 'The value must not be blank',
            self::NAMED => '{{name}} must not be blank',
        ),
        self::MODE_NEGATIVE => array(
            self::STANDARD => 'The value must be blank',
            self::NAMED => '{{name}} must be blank',
        )
    );

    public function chooseTemplate()
    {
        return $this->getName() == "" ? static::STANDARD : static::NAMED;
    }
}

