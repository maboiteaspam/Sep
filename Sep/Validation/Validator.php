<?php
namespace Sep\Validation;

use \Respect\Validation\Validator as V;
use \Respect\Validation\Validatable as Validatable;
use \Respect\Validation\Exceptions\ComponentException as ComponentException;

/**
 * @method \Sep\Validation\Validator notBlank()
 * @method \Sep\Validation\Validator notRegex($regex)
 * @method \Sep\Validation\Validator notHTML()
 */
class Validator extends V{
    public static function buildRule($ruleSpec, $arguments=array())
    {
        try{
            return parent::buildRule($ruleSpec, $arguments);
        }catch(ComponentException $ex ){

            if ($ruleSpec instanceof Validatable) {
                return $ruleSpec;
            }

            try {
                $validatorFqn = 'Sep\\Validation\\Rules\\' . ucfirst($ruleSpec);
                $validatorClass = new \ReflectionClass($validatorFqn);
                $validatorInstance = $validatorClass->newInstanceArgs(
                    $arguments
                );
                return $validatorInstance;
            } catch (\ReflectionException $e) {
                throw new ComponentException($e->getMessage());
            }
        }
    }
} 