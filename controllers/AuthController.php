<?php

declare(strict_types=1);

namespace app\controllers;

use app\core\Application;
use app\core\Controller;
use app\core\middlewares\AuthMiddleware;
use app\core\Request;
use app\core\Response;
use app\models\LoginForm;
use app\models\RefreshToken;
use app\models\User;

class AuthController extends Controller
{
    public function __construct()
    {
        //$this->registerMiddleware(new AuthMiddleware(['user']));
    }

    public function login(Request $request)
    {
        if ($request->isPost() === false) return;

        $loginForm = new LoginForm();
        $loginForm->loadData($request->getBody());

        if ($loginForm->validate() && ($token = $loginForm->login()) !== false) {
            return $token;
        }

        return $loginForm->formatErrors();
    }

    public function register(Request $request)
    {
        $user = new User();

        if ($request->isPost()) {
            $user->loadData($request->getBody());

            if ($user->validate() && $user->save()) {
                return Application::$app->login($user);
            }

            return $user->formatErrors();
        }
    }


    public function tokenRefresh(Request $request)
    {
        if ($request->isPost() === false) return;

        $refreshToken = new RefreshToken();
        $refreshToken->loadData($request->getBody());

        if ($refreshToken->validate()) {
            $user = User::findOne(['id' => $refreshToken->user_id]);
            return Application::$app->generateJWT($user);
        }

        return $refreshToken->formatErrors();
    }
}
