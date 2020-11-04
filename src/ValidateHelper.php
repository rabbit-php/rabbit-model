<?php

declare(strict_types=1);

namespace Rabbit\Model;

use Rabbit\Base\Helper\ArrayHelper;
use Respect\Validation\Validatable;

/**
 * Class ValidateHelper
 * @package Rabbit\Model
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
            foreach ($properties as $property) {
                if (!empty($attributeNames) && !in_array($property, $attributeNames)) {
                    continue;
                }
                if ($validator instanceof Validatable) {
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
        $errors = [];
        foreach ($error as $name => $es) {
            if (!empty($es)) {
                $errors[$name] = reset($es);
            }
        }
        return implode(PHP_EOL, $errors);
    }
}
