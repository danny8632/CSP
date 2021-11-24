<?php

declare(strict_types=1);

namespace app\models;

use app\core\UserModel;

class User extends UserModel
{
    const TYPE_EMPLOYEE = 'employee';
    const TYPE_ADMIN    = 'admin';

    public int    $id              = 0;
    public string $username        = '';
    public string $password        = '';
    public string $confirmPassword = '';
    public string $firstname       = '';
    public string $lastname        = '';
    public string $type            = self::TYPE_EMPLOYEE;
    public float  $requiredhours   = 0;
    public bool   $monthlypay      = false;


    public function tableName(): string
    {
        return 'Users';
    }


    public function primaryKey(): string
    {
        return 'id';
    }


    public function save()
    {
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        return parent::save();
    }


    public function rules(): array
    {
        return [
            'username'        => [self::RULE_REQUIRED],
            'password'        => [self::RULE_REQUIRED, [self::RULE_MIN, 'min' => 8], [self::RULE_MAX, 'max' => 30]],
            'confirmPassword' => [self::RULE_REQUIRED, [self::RULE_MACTH, 'match' => 'password']],
            'firstname'       => [self::RULE_REQUIRED],
            'lastname'        => [self::RULE_REQUIRED],
            'type'            => [self::RULE_REQUIRED],
            'requiredhours'   => [self::RULE_REQUIRED, self::RULE_FLOAT],
            'monthlypay'      => [self::RULE_REQUIRED, self::RULE_BOOL],

        ];
    }

    public function attributes(): array
    {
        return ['username', 'password', 'firstname', 'lastname', 'type', 'requiredhours', 'monthlypay'];
    }


    public function properties(): array
    {
        return ['username', 'firstname', 'lastname', 'type', 'requiredhours', 'monthlypay'];
    }


    public function labels(): array
    {
        return [
            'firstname'       => 'First name',
            'lastname'        => 'Last name',
            'username'        => 'Username',
            'password'        => 'Password',
            'type'            => 'Type',
            'requiredhours'   => 'Required hours',
            'monthlypay'      => 'Monthly paid',
            'confirmPassword' => 'Confirm password',
        ];
    }


    public function getDisplayName(): string
    {
        return $this->firstname . ' ' . $this->lastname;
    }
}
