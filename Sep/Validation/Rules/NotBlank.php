<?php
namespace Sep\Validation\Rules;

use Respect\Validation\Rules\NotEmpty;

class NotBlank extends NotEmpty
{
    public function validate($input)
    {
        if (is_string($input)) {
            $input = trim($input);
            return $input!=="";
        }

        return !empty($input);
    }
}

