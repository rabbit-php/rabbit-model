<?php


namespace rabbit\model;

use rabbit\contract\ArrayableTrait;

/**
 * Class Model
 * @package rabbit\model
 */
abstract class Model
{
    use ArrayableTrait;
    /** @var array */
    protected $_errors;

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
    public function load(array $columns): self
    {
        foreach ($columns as $name => $value) {
            if (property_exists($this, $name)) {
                $this->$name = $value;
            } else {
                $this->attributes[$name] = $value;
            }
        }
        return $this;
    }

    /**
     * @return array
     */
    abstract public static function rules(): array;

    /**
     * @param string|null $attribute
     */
    public function clearErrors(string $attribute = null)
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
    public function addError(string $attribute, string $error = '')
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
     * @return array|mixed
     */
    public function getErrors(string $attribute = null)
    {
        if ($attribute === null) {
            return $this->_errors ?? [];
        }

        return isset($this->_errors[$attribute]) ? $this->_errors[$attribute] : [];
    }

    /**
     * @param string $attribute
     * @return mixed|null
     */
    public function getFirstError(string $attribute)
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
    public function validate(
        array $attributeNames = null,
        bool $throwAble = true,
        bool $firstReturn = false,
        bool $clearErrors = true
    ) {
        if ($clearErrors) {
            $this->clearErrors();
        }

        $errors = ValidateHelper::validate(
            $this->attributes,
            static::rules(),
            $throwAble,
            $firstReturn,
            $attributeNames
        );
        $this->addErrors($errors);

        return !$this->hasErrors();
    }
}
