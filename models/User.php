<?php

declare(strict_types=1);

namespace app\models;

use app\core\UserModel;

class User extends UserModel
{
    const TYPE_EMPLOYEE = 'employee';
    const TYPE_ADMIN    = 'admin';

    public int    $id              = -1;
    public string $username        = '';
    public string $password        = '';
    public string $firstname       = '';
    public string $lastname        = '';
    public string $type            = self::TYPE_EMPLOYEE;
    public float  $requiredhours   = 0;
    public bool   $monthlypay      = false;
    public array  $departments     = [];


    public function tableName(): string
    {
        return 'Users';
    }


    public function primaryKey(): string
    {
        return 'id';
    }


    public function getData(): array
    {
        $departmentRelation = DepartmentRelation::findAll([['user_id', '=', $this->id]]);

        $this->departments = array_map(fn($dep) => $dep['department_id'], $departmentRelation);

        return parent::getData();
    }


    public function save()
    {
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        return parent::save();
    }

    public function update(?array $data = null)
    {
        if(isset($data['password'])) {
            $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        }
        return parent::update($data);
    }



    public function rules(): array
    {
        return [
            'username'        => [self::RULE_REQUIRED, [self::RULE_UNIQUE, 'class' => User::class]],
            'password'        => [self::RULE_REQUIRED, [self::RULE_MIN, 'min' => 8], [self::RULE_MAX, 'max' => 30]],
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
        return ['id', 'username', 'firstname', 'lastname', 'type', 'requiredhours', 'monthlypay', 'departments'];
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
        ];
    }


    public function getDisplayName(): string
    {
        return $this->firstname . ' ' . $this->lastname;
    }

    public function isAdmin(): bool
    {
        return $this->type === self::TYPE_ADMIN;
    }
}
