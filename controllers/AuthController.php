<?php

declare(strict_types=1);

namespace app\controllers;

use app\core\Application;
use app\core\Controller;
use app\core\middlewares\AuthMiddleware;
use app\core\Request;
use app\core\Response;
use app\models\LoginForm;
use app\models\User;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->registerMiddleware(new AuthMiddleware(['user']));
    }

    public function login(Request $request, Response $response)
    {
        $loginForm = new LoginForm();


        if($request->isPost())
        {
            $loginForm->loadData($request->getBody());

            if($loginForm->validate() && $token = $loginForm->login())
            {
                return [
                    'token' => $token
                ];
            }

            return $loginForm->formatErrors();
        }
    }

    public function register(Request $request)
    {
        $user = new User();

        if($request->isPost())
        {
            $user->loadData($request->getBody());

            if($user->validate() && $user->save())
            {
                return [
                    'token' => Application::$app->login($user)
                ];
            }

            return $user->formatErrors();
        }
    }


    public function user()
    {
        return Application::$app->user;
    }
}