<?php


namespace rabbit\model;

use rabbit\helper\ArrayHelper;
use Respect\Validation\Validatable;

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
                if ($validator instanceof Validatable) {
                    if (!$validator->validate(ArrayHelper::getValue($attributes, $property))) {
                        $exception = $validator->reportError($property);
                        $msg = $exception->getMessage();
                        if (isset($errors[$property]) && $errors[$property] !== $msg) {
                            $errors[$property] = $errors[$property] . BREAKS . $msg;
                        } else {
                            $errors[$property] = $msg;
                        }
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

        if ($throwAble && $errors) {
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