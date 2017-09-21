<?php
/*
 * This file is part of the Engine framework.
 * (c) Mathias Methner <mathiasmethner@gmail.com>
 * Please view the LICENSE file
 */
namespace Engine\Core;

class Validation
{

    const WRONG_VALUE = 'value';

    const WRONG_PARAM = 'param';

    /**
     *
     * @var array
     */
    protected $definition = [];

    /**
     *
     * @var array
     */
    protected $validatedData = [];

    /**
     *
     * @var array
     */
    protected $unvalidatedData = [];

    /**
     *
     * @var array
     */
    protected $errors = [];

    /**
     *
     * @var array
     */
    protected $resets = [];

    /**
     *
     * @return Validation
     */
    public function __construct()
    {
        $this->errors[static::WRONG_VALUE] = [];
        $this->errors[static::WRONG_PARAM] = [];
    }

    /**
     *
     * @return array
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     *
     * @return array
     */
    public function getErrors()
    {
        $return = [];
        foreach (array_keys($this->errors) as $errors) {
            foreach ($this->errors[$errors] as $key => $error) {
                $return[$key] = $error;
            }
        }
        return $return;
    }

    /**
     * returns all field ids with error and prepares messages for notification widget
     *
     * @return array
     */
    public function forAjax()
    {
        $fields = [];
        array_walk_recursive($this->errors, function ($message, $key) use (&$fields) {
            $fields[] = $key;
            if ($message !== '') {
                // @todo no core
                //Notification::warning($message);
            }
        });

        return $fields;
    }

    /**
     * @param bool $withResets
     * @return array
     */
    public function getValidatedData($withResets = false)
    {
        $ret = $this->validatedData;

        if ($withResets) {
            $ret = array_merge($this->resets, $ret);
        }
        return $ret;
    }

    /**
     * @param string $key
     * @return string|array
     */
    public function getValidated($key)
    {
        $default = strpos($this->definition[$key]['validator'], 'array') !== false ? [] : '';
        return isset($this->validatedData[$key]) ? $this->validatedData[$key] : $default;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function modifyValidated($key, $value)
    {
        if (isset($this->validatedData[$key])) {
            $this->validatedData[$key] = $value;
        }
    }

    /**
     *
     * @param array $unvalidatedData
     * @return bool
     */
    public function validate(array $unvalidatedData)
    {
        if (empty($this->definition)) {
            return true;
        }

        $this->unvalidatedData = $unvalidatedData;

        foreach ($this->definition as $key => $definition) {

            if ($definition['required'] && !array_key_exists($key, $unvalidatedData)) {
                $this->errors[static::WRONG_PARAM][$key] = $this->definition[$key]['msg'];
                continue;
            }

            if (!$definition['required'] && !array_key_exists($key, $unvalidatedData)) {
                continue;
            }

            if ($definition['required'] && $unvalidatedData[$key] === '') {
                $this->errors[static::WRONG_PARAM][$key] = $this->definition[$key]['msg'];
                continue;
            }

            if (!$definition['required'] && $unvalidatedData[$key] == '') {
                $this->resets[$key] = '';
                continue;
            }

            if (!array_key_exists('validator', $definition)) {
                continue;
            }

            switch ($definition['validator']) {
                case 'date':
                    $valid = $this->validateDate($unvalidatedData[$key]);
                    break;

                case 'time':
                    $valid = $this->validateTime($unvalidatedData[$key]);
                    break;

                case 'money':
                    $valid = $this->validateMoney($unvalidatedData[$key]);
                    break;

                case 'money-unsigned':
                    $valid = $this->validateMoneyUnsigned($unvalidatedData[$key]);
                    break;

                case 'string':
                case 'wysiwyg':
                    $valid = $this->validateString($unvalidatedData[$key]);
                    break;

                case 'phone':
                    $valid = $this->validatePhone($unvalidatedData[$key]);
                    break;

                case 'numeric':
                    $valid = $this->validateNumeric($unvalidatedData[$key]);
                    break;

                case 'bool':
                    $valid = $this->validateBoolean($unvalidatedData[$key]);
                    break;

                case 'password':
                    $valid = $this->validatePassword($unvalidatedData[$key]);
                    break;

                case 'url':
                    $valid = $this->validateUrl($unvalidatedData[$key]);
                    break;

                case 'upload':
                    $valid = $this->validateUpload($unvalidatedData[$key]);
                    break;

                case 'email':
                    $valid = $this->validateEmail($unvalidatedData[$key]);
                    break;

                case 'array':
                case 'array:int':
                case 'array:string':
                    $valid = $this->validateArray($unvalidatedData[$key], $definition['validator']);
                    break;

                case 'choice':
                    if (!isset($definition['choices'])) {
                        $valid = false;
                    } else {
                        $valid = $this->validateChoice($unvalidatedData[$key], $definition['choices']);
                    }
                    break;

                default:
                    $valid = false;
            }

            if (!$valid) {
                $this->errors[static::WRONG_VALUE][$key] = $this->definition[$key]['msg'];
            }

            if ($definition['validator'] == 'wysiwyg') {
                $this->validatedData[$key] = $unvalidatedData[$key];
            } else {
                $this->validatedData[$key] = $this->escape($unvalidatedData[$key]);
            }
        }

        return !$this->hasErrors();
    }

    /**
     *
     * @param string $value
     *            dd.mm.YYYY
     * @return bool
     */
    protected function validateDate($value)
    {
        return preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $value);
    }

    /**
     *
     * @param string $value hh:mm
     * @return bool
     */
    protected function validateTime($value)
    {
        return preg_match('/^(2[0-3]|[0-1]{0,1}[0-9]):[0-5][0-9]$/', $value);
    }

    /**
     *
     * @param decimal $value
     * @return bool
     */
    protected function validateMoney($value)
    {
        return preg_match('/^([-]?\d{1,10})(\.|\,)(\d{2})$/', $value);
    }

    /**
     *
     * @param decimal $value
     * @return bool
     */
    protected function validateMoneyUnsigned($value)
    {
        return preg_match('/^(\d{1,10})(\.|\,)(\d{2})$/', $value);
    }

    /**
     *
     * @param string $value
     * @return bool
     */
    protected function validateString($value)
    {
        return is_string($value);
    }

    /**
     *
     * @param string $value
     * @return bool
     */
    protected function validatePhone($value)
    {
        // return preg_match('/^[\+][0-9\/\.\- ]{1,20}+$/', $value);
        return preg_match('/[\+0-9\/\.\- ]{1,20}+$/', $value);
    }

    /**
     *
     * @param mixed $value
     * @return bool
     */
    protected function validateNumeric($value)
    {
        return is_numeric($value);
    }

    /**
     *
     * @param mixed $value
     * @return bool
     */
    protected function validateBoolean($value)
    {
        return in_array($value, [
            0,
            '0',
            1,
            '1',
            true,
            false,
            'true',
            'false'
        ], true);
    }

    /**
     *
     * @param mixed $value
     * @return bool
     */
    protected function validatePassword($value)
    {
        return preg_match('/(?=^.{10,}$)((?=.*\d)|(?=.*\W+))(?![.\n])(?=.*[A-Z])(?=.*[a-z]).*$/', $value);
    }

    /**
     *
     * @param mixed $value
     * @return bool
     */
    protected function validateUrl($value)
    {
        return preg_match('/^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*(?:.[A-Z0-9][A-Z0-9_-]*)+):?(d+)?\/?/i', $value);
    }

    /**
     *
     * @param mixed $value
     * @return bool
     */
    protected function validateUpload($value)
    {
        return is_array($value);
    }

    /**
     *
     * @param mixed $value
     * @return bool
     */
    protected function validateEmail($value)
    {
        return preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i', $value);
    }

    /**
     *
     * @param mixed $values
     * @param mixed $type
     * @return bool
     */
    protected function validateArray($values, $type)
    {
        if (!is_array($values)) {
            return false;
        }

        switch ($type) {
            case 'array:int':
                foreach (array_values($values) as $value) {
                    if (!$this->validateNumeric($value)) {
                        return false;
                    }
                }
                break;
            case 'array:string':
                foreach (array_values($values) as $value) {
                    if (!$this->validateString($value)) {
                        return false;
                    }
                }
                break;
        }

        return true;
    }

    /**
     *
     * @param mixed $value
     * @param array $choices
     * @return bool
     */
    protected function validateChoice($value, array $choices = [])
    {
        return in_array($value, $choices);
    }

    /**
     *
     * @param string $value
     * @return string
     */
    protected function escape($value)
    {
        if (is_array($value)) {
            foreach ($value as $key => $arrayValue) {
                $value[$key] = $this->escape($arrayValue);
            }
        } else {
            $value = htmlspecialchars($value);
        }
        return $value;
    }

    /**
     *
     * @return boolean
     */
    public function hasErrors()
    {
        foreach (array_keys($this->errors) as $errors) {
            if (!empty($this->errors[$errors])) {
                return true;
            }
        }
        return false;
    }
}
