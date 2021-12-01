<?php

declare(strict_types=1);

namespace app\core;

use DateTime;

/**
 * This is the base Model class
 */
abstract class Model
{
    public const TIMESTAMP_FORMAT = 'Y-m-d H:i:s';

    //  Defining const for the validation
    public const RULE_REQUIRED = 'required';
    public const RULE_EMAIL    = 'email';
    public const RULE_MIN      = 'min';
    public const RULE_MAX      = 'max';
    public const RULE_MATCH    = 'macth';
    public const RULE_UNIQUE   = 'unique';
    public const RULE_INT      = 'int';
    public const RULE_FLOAT    = 'float';
    public const RULE_BOOL     = 'bool';
    public const RULE_DATETIME = 'datetime';

    /**
     * The error array is going to contain all the errors that the
     * validation function is going to generate.
     *
     * @var array
     */
    private array $errors = [];


    /**
     * This function will populate the $this->* variables of the Model class.
     * fx. If the User model has a property: firstname and you parse [ 'firstname' => 'Alan' ]
     * then the (User/$this)->firstname will be Alan.
     * 
     * Often will the data come from the request data.
     *
     * @param array $data
     * @return void
     */
    public function loadData(array $data): void
    {
        $rules = $this->rules();

        //  Goes through each $data and checking if the property exists
        //  in the current model.
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {

                if (isset($rules[$key]) && in_array(self::RULE_DATETIME, $rules[$key])) {
                    if (strlen($value) === 10) {
                        $value = DateTime::createFromFormat('Y-m-d', $value);
                    } else {
                        $value = DateTime::createFromFormat(self::TIMESTAMP_FORMAT, $value);
                    }
                }

                $this->{$key} = $value;
            }
        }
    }


    /**
     * The rules function is required by all classes that extends Model class.
     * The rules class returns an array defining all the rules for that models property.
     * 
     * Heres an example of how the User model rules might look like: 
     * [
     *      'firstname'       => [self::RULE_REQUIRED],
     *      'lastname'        => [self::RULE_REQUIRED],
     *      'password'        => [self::RULE_REQUIRED, [self::RULE_MIN, 'min' => 8], [self::RULE_MAX, 'max' => 30]],
     *      'email'           => [self::RULE_REQUIRED, self::RULE_EMAIL, [ self::RULE_UNIQUE, 'class' => self::class ]],
     *      'confirmPassword' => [self::RULE_REQUIRED, [self::RULE_MATCH, 'match' => 'password']],
     * ]
     *
     * @return array
     */
    abstract public function rules(): array;


    /**
     * This should return a list of all "public"/"selectable" properties in your class
     * So fx the User class has "confirmPassword" we don't want that data to get to the frontend
     * so this should not be returned.
     *
     * @return array
     */
    abstract public function properties(): array;


    /**
     * The Labels function should return a "translated" or prettified label for
     * each property in the model, for form generation. Otherwise it will default to
     * the property name when rendered
     *
     * @return array
     */
    public function labels(): array
    {
        return [];
    }


    /**
     * A getter for a specific label. Will default to the property.
     *
     * @param string $property The property name
     * @return string The label for the property
     */
    public function getLabel(string $property): string
    {
        return $this->labels()[$property] ?? $property;
    }


    /**
     * This is the validate function. This should have a validation for each rule
     * Specified as static rules fx: RULE_REQUIRED or RULE_MATCH
     *
     * @return bool returns true if theres no errors
     */
    public function validate(?array $data = null): bool
    {
        $classRules = $this->rules();
        
        if ($data !== null) {
            $dataAttributes = array_keys($data);
            foreach ($classRules as $ruleKey => $ruleValue) {
                if (!in_array($ruleKey, $dataAttributes)) {
                    unset($classRules[$ruleKey]);
                }
            }
        }
        
        foreach ($classRules as $attribute => $rules) {
            $value = $data !== null ? $data[$attribute] : $this->{$attribute};
            foreach ($rules as $rule) {
                $ruleName = $rule;

                if (!is_string($ruleName)) {
                    $ruleName = $rule[0];
                }

                if ($ruleName === self::RULE_REQUIRED && !isset($value)) {
                    $this->addErrorForRule($attribute, self::RULE_REQUIRED);
                }

                if ($ruleName === self::RULE_EMAIL && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addErrorForRule($attribute, self::RULE_EMAIL);
                }

                if ($ruleName === self::RULE_MIN && strlen($value) < $rule['min']) {
                    $this->addErrorForRule($attribute, self::RULE_MIN, $rule);
                }

                if ($ruleName === self::RULE_MAX && strlen($value) > $rule['max']) {
                    $this->addErrorForRule($attribute, self::RULE_MAX, $rule);
                }

                if ($ruleName === self::RULE_MATCH && $value !== $this->{$rule['match']}) {
                    $rule['match'] = $this->getLabel($rule['match']);
                    $this->addErrorForRule($attribute, self::RULE_MATCH, $rule);
                }

                if ($ruleName === self::RULE_UNIQUE) {
                    $className  = $rule['class'];
                    $uniqueAttr = $rule['attribute'] ?? $attribute;
                    $tableName  = $className::tableName();

                    $statement = Application::$app->db->prepare("SELECT * FROM $tableName WHERE $uniqueAttr = :attr");
                    $statement->bindValue(":attr", $value);
                    $statement->execute();
                    $record = $statement->fetchObject();

                    if ($record) {
                        $this->addErrorForRule($attribute, self::RULE_UNIQUE, ['field' => $this->getLabel($attribute)]);
                    }
                }

                if ($ruleName === self::RULE_INT) {
                    $this->{$attribute} = filter_var($value, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);

                    if (is_null($this->{$attribute})) {
                        $this->addErrorForRule($attribute, self::RULE_INT);
                    }
                }

                if ($ruleName === self::RULE_FLOAT) {
                    $this->{$attribute} = filter_var($value, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE);

                    if (is_null($this->{$attribute})) {
                        $this->addErrorForRule($attribute, self::RULE_FLOAT);
                    }
                }

                if ($ruleName === self::RULE_BOOL) {
                    $this->{$attribute} = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

                    if (is_null($this->{$attribute})) {
                        $this->addErrorForRule($attribute, self::RULE_BOOL);
                    }
                }
            }
        }

        return empty($this->errors);
    }


    /**
     * This function handles the creation of nice error texts when validating.
     *
     * @param string $property The name of the property value that is incorrect
     * @param string $rule The rule that triggered the error
     * @param array $params An array containg the fx min as key and 8 as value to replace in the error text
     * @return void
     */
    private function addErrorForRule(string $property, string $rule, array $params = []): void
    {
        $message = $this->errorMessage()[$rule] ?? '';

        foreach ($params as $key => $value) {
            $message = str_replace("{{$key}}", strval($value), strval($message));
        }

        $this->errors[$property][] = $message;
    }


    /**
     * This is for adding custom error messages outside the validation function.
     *
     * @param string $property The name of the property that the error is being added to
     * @param string $message The actual error message.
     * @return void
     */
    public function addError(string $property, string $message): void
    {
        $this->errors[$property][] = $message;
    }


    /**
     * Returns all the "default" error messages with "translated" text.
     * **NOTE** This could have ben a const or class variable
     *
     * @return array
     */
    private function errorMessage(): array
    {
        return [
            self::RULE_REQUIRED => 'This field is required',
            self::RULE_EMAIL    => 'This field must be a valid email address',
            self::RULE_MIN      => 'Min length of this field must be {min}',
            self::RULE_MAX      => 'Max length of this field must be {max}',
            self::RULE_MATCH    => 'This field must be the same as {match}',
            self::RULE_UNIQUE   => 'Record with this {field} already exists',
            self::RULE_INT      => 'This must be of type int',
            self::RULE_FLOAT    => 'This must be of type float',
            self::RULE_BOOL     => 'This must be of type boolean',
            self::RULE_DATETIME => 'This must be of type string looking like : 2021-01-01',
        ];
    }


    /**
     * Returns true if theres any errors on a given property.
     *
     * @param string $property the name of the property
     * @return bool
     */
    public function hasError(string $property): bool
    {
        return isset($this->errors[$property]);
    }


    /**
     * This will get the first error on a property. Often times it is only 
     * necessary to show the first error to the user instead of all the errors.
     *
     * @param string $property the name of the property fx: firstname
     * @return string|boolean returns false if theres no errors otherwise the error text will be returned
     */
    public function getFirstError(string $property): string|bool
    {
        return $this->errors[$property][0] ?? false;
    }


    public function formatErrors(): array
    {
        $response = [];

        foreach (get_object_vars($this) as $key => $value) {
            if ($this->hasError($key)) {
                $response[$key] = $this->getFirstError($key);
            }
        }

        return $response;
    }


    public function getData(): array
    {
        $response = [];

        foreach ($this->properties() as $key) {
            if (isset($this->{$key})) {
                $value = $this->{$key};
                if (is_a($this->{$key}, DateTime::class)) {
                    $value = $value->format(self::TIMESTAMP_FORMAT);
                }
                $response[$key] = $value;
            }
        }

        return $response;
    }
}
