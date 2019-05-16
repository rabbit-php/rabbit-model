<?php


namespace rabbit\model;

use rabbit\helper\ArrayHelper;
use Respect\Validation\Validator;

defined('BREAKS') or define('BREAKS', PHP_SAPI === 'cli' ? PHP_EOL : '</br>');

/**
 * Class ValidateHelper
 * @package rabbit\model
 */
class ValidateHelper
{
    /**
     * @param array $attributes
     * @param array $rules
     * @param bool $firstReturn
     * @param bool $throwAble
     * @param array|null $attributeNames
     * @return array
     */
    public static function validate(
        array &$attributes,
        array $rules,
        bool $firstReturn = false,
        bool $throwAble = true,
        array $attributeNames = null
    ): array {
        $errors = [];
        foreach ($rules as $rule) {
            list($properties, $validator) = $rule;
            $errProperties = [];
            foreach ($properties as $property) {
                if (!empty($attributeNames) && !in_array($property, $attributeNames)) {
                    continue;
                }
                if ($validator instanceof Validator) {
                    if (!$validator->validate(ArrayHelper::getValue($attributes, $property))) {
                        $exception = $validator->reportError($property);
                        $errors[$property] = $exception->getMessage();
                        if ($firstReturn) {
                            if ($throwAble) {
                                throw $exception;
                            } else {
                                return $errors;
                            }
                        }
                        $errProperties[] = $property;
                    }
                } elseif (is_callable($validator)) {
                    $attributes[$property] = call_user_func($validator);
                } else {
                    !isset($attributes[$property]) && $attributes[$property] = $validator;
                }
            }
        }

        if ($throwAble) {
            throw new \InvalidArgumentException(self::getErrorString($errors));
        }
        return $errors;
    }

    /**
     * @param array $error
     * @return string
     */
    public static function getErrorString(array $error): string
    {
        return implode(BREAKS, $error);
    }
}