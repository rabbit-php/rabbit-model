<?php

declare(strict_types=1);

namespace Rabbit\Model;

use Rabbit\Base\Core\BaseObject;

/**
 * Class Model
 * @package Rabbit\Model
 */
abstract class Model extends BaseObject
{
    private array $_errors;

    /**
     * Model constructor.
     * @param array $columns
     */
    public function __construct(array $columns = [])
    {
        $this->load($columns);
    }

    /**
     * @param array $columns
     * @return Model
     */
    public function load(array $columns): bool
    {
        foreach ($columns as $name => $value) {
            if (property_exists($this, $name)) {
                $this->$name = $value;
            }
        }
        return true;
    }

    /**
     * @return array
     */
    abstract public function rules(): array;

    /**
     * @param string|null $attribute
     */
    public function clearErrors(string $attribute = null): void
    {
        if ($attribute === null) {
            $this->_errors = [];
        } else {
            unset($this->_errors[$attribute]);
        }
    }

    /**
     * @param string $attribute
     * @param string $error
     */
    public function addError(string $attribute, string $error = ''): void
    {
        $this->_errors[$attribute][] = $error;
    }

    /**
     * @param array $errors
     */
    public function addErrors(array $errors): void
    {
        foreach ($errors as $attribute => $msg) {
            $this->addError($attribute, $msg);
        }
    }

    /**
     * @param string|null $attribute
     * @return array
     */
    public function getErrors(string $attribute = null): array
    {
        if ($attribute === null) {
            return $this->_errors ?? [];
        }

        return isset($this->_errors[$attribute]) ? $this->_errors[$attribute] : [];
    }

    /**
     * @param string $attribute
     * @return string|null
     */
    public function getFirstError(string $attribute): ?string
    {
        return isset($this->_errors[$attribute]) ? reset($this->_errors[$attribute]) : null;
    }

    /**
     * @return array
     */
    public function getFirstErrors(): array
    {
        if (empty($this->_errors)) {
            return [];
        }

        $errors = [];
        foreach ($this->_errors as $name => $es) {
            if (!empty($es)) {
                $errors[$name] = reset($es);
            }
        }

        return $errors;
    }

    /**
     * @param string|null $attribute
     * @return bool
     */
    public function hasErrors(string $attribute = null): bool
    {
        return $attribute === null ? !empty($this->_errors) : isset($this->_errors[$attribute]);
    }

    /**
     * @param array|null $attributeNames
     * @param bool $throwAble
     * @param bool $firstReturn
     * @param bool $clearErrors
     * @return bool
     */
    public function validate(array $attributeNames = null, bool $throwAble = true, bool $firstReturn = false, bool $clearErrors = true): bool
    {
        if ($clearErrors) {
            $this->clearErrors();
        }

        $arr = get_object_vars($this);

        $errors = ValidateHelper::validate(
            $arr,
            $this->rules(),
            $throwAble,
            $firstReturn,
            $attributeNames
        );
        $this->addErrors($errors);

        return !$this->hasErrors();
    }
}
