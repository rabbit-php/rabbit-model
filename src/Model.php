<?php


namespace rabbit\validation;


use rabbit\contract\ArrayableTrait;
use Respect\Validation\Validator;

defined('BREAKS') or define('BREAKS', PHP_SAPI === 'cli' ? PHP_EOL : '</br>');

/**
 * Class Model
 * @package rabbit\validation
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
            $this->attributes[$name] = $value;
        }
        return $this;
    }

    /**
     * @return array
     */
    abstract function rules(): array;

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
     * @param bool $firstReturn
     * @param bool $clearErrors
     * @return bool
     */
    public function validate(array $attributeNames = null, bool $firstReturn = false, bool $clearErrors = true)
    {
        if ($clearErrors) {
            $this->clearErrors();
        }

        if (empty($attributeNames)) {
            $attributeNames = array_keys($this->attributes);
        }

        foreach ($this->rules() as $rule) {
            list($properties, $validator) = $rule;
            foreach ($properties as $property) {
                if (!in_array($property, $attributeNames)) {
                    continue;
                }
                if ($validator instanceof Validator) {
                    if (!$validator->validate($this->attributes[$property])) {
                        $this->addError($property, $validator->reportError($property)->getMessage());
                        if ($firstReturn) {
                            return false;
                        }
                    }
                } elseif (is_callable($validator)) {
                    $this->attributes[$property] = call_user_func($validator);
                } else {
                    !isset($this->attributes[$property]) && $this->attributes[$property] = $validator;
                }
            }
        }

        return !$this->hasErrors();
    }
}