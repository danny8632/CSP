<?php

declare(strict_types=1);

namespace app\controllers;

use app\core\Application;
use app\core\Controller;
use app\core\exception\ForbiddenException;
use app\core\exception\NotFoundException;
use app\core\middlewares\AuthMiddleware;
use app\core\Request;
use app\models\User;

class UserController extends Controller
{
    public function __construct()
    {
        $this->registerMiddleware(new AuthMiddleware(['get', 'post', 'delete']));
    }

    public function get(Request $request)
    {
        $data = $request->getBody();
        $user = Application::$app->user;

        if (isset($data['id'])) {
            $id = intval($data['id']);

            if ($user->isAdmin() || $id === $user->id) {
                return User::findOne(['id' => $id])->getData();
            }
            throw new ForbiddenException;
        }

        if ($user->isAdmin() === false) {
            throw new ForbiddenException;
        }

        return array_map(fn ($user) => $user->getData(), User::findAll([['1', '=', '1']]));
    }

    public function post(Request $request)
    {
        if ($request->isPost() === false) {
            throw new NotFoundException;
        }

        //  security
        if (!Application::$app->user->isAdmin()) {
            throw new ForbiddenException;
        }

        $user = new User();
        $user->loadData($request->getBody());

        if ($user->validate() && $user->save()) {
            return $user;
        }

        return $user->formatErrors();
    }

    public function put(Request $request)
    {
        if ($request->isPut() === false) {
            throw new NotFoundException;
        }

        $data = $request->getBody();

        if (!isset($data['id'])) {
            throw new NotFoundException;
        }

        $user = User::findOne(['id' => intval($data['id'])]);

        if ($user === false) {
            throw new NotFoundException;
        }

        $user->loadData($data);

        if ($user->validate($data) && $user->update($data)) {
            return $user->getData();
        }

        return $user->formatErrors();
    }

    public function delete(Request $request)
    {
        if ($request->isDelete() === false) return;

        //  security
        if (!Application::$app->user->isAdmin()) {
            throw new ForbiddenException;
        }

        $data = $request->getBody();

        if (!isset($data['id'])) {
            return "You must parse the id of the relation";
        }

        $user = User::findOne(['id' => intval($data['id'])]);

        if ($user === false) {
            throw new NotFoundException;
        }

        if ($user->delete()) {
            return true;
        }

        return $user->formatErrors();
    }
}
