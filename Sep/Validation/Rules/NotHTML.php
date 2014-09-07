<?php
namespace Sep\Validation\Rules;

class NotHTML extends NotRegex
{
    public $regex;

    public function __construct()
    {
        $this->regex = "/(<[\/]?[a-z]+([^>]+>|>))/";
    }

    public function validate($input)
    {
        return !( (bool) preg_match($this->regex, $input) );
    }
}

