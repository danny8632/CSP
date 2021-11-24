<?php

declare(strict_types=1);

namespace app\models;

use app\core\Application;
use app\core\Model;


class LoginForm extends Model
{
    public string $username = '';
    public string $password = '';


    public function rules(): array
    {
        return [
            'username' => [self::RULE_REQUIRED],
            'password' => [self::RULE_REQUIRED],
        ];
    }

    public function properties(): array
    {
        return ['username', 'password'];
    }


    public function labels(): array
    {
        return [
            'username' => 'Username',
            'password' => 'Password',
        ];
    }


    public function login(): array|bool
    {
        $user = User::findOne(['username' => $this->username]);

        if (!$user) {
            $this->addError('username', 'User does not exist with this username');
            return false;
        }

        if (!password_verify($this->password, $user->password)) {
            $this->addError('password', 'Login is incorrect');
            return false;
        }

        return Application::$app->login($user);
    }
}
