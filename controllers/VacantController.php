<?php

declare(strict_types=1);

namespace app\controllers;

use app\core\Application;
use app\core\Controller;
use app\core\exception\ForbiddenException;
use app\core\exception\NotFoundException;
use app\core\middlewares\AuthMiddleware;
use app\core\Request;
use app\models\Vacant;

class VacantController extends Controller
{
    public function __construct()
    {
        $this->registerMiddleware(new AuthMiddleware(['get', 'post', 'put', 'delete']));
    }

    public function get(Request $request)
    {
        if ($request->isGet() === false) {
            throw new NotFoundException;
        }

        $data = $request->getBody();
        $user = Application::$app->user;

        if (isset($data['id'])) {
            $id = intval($data['id']);

            if ($user->isAdmin()) {
                return Vacant::findOne(['id' => $id])->getData();
            } else {
                return Vacant::findOne(['id' => $id, "user_id" => $user->id])->getData();
            }
        }

        if (isset($data['user_id'])) {
            $user_id = intval($data['user_id']);

            if (!$user->isAdmin() && $user_id !== $user->id) {
                throw new ForbiddenException;
            }

            return Vacant::findAll([['user_id', '=', $user_id]]);
        }

        if ($user->isAdmin() === false) {
            throw new ForbiddenException;
        }

        return Vacant::findAll([['1', '=', '1']]);
    }

    public function post(Request $request)
    {
        if ($request->isPost() === false) {
            throw new NotFoundException;
        }

        if (!Application::$app->user->isAdmin()) {
            throw new ForbiddenException;
        }

        $vacant = new Vacant();
        $vacant->loadData($request->getBody());

        if ($vacant->validate() && $vacant->save()) {
            return $vacant->getData();
        }

        return $vacant->formatErrors();
    }

    public function put(Request $request)
    {
        if ($request->isPut() === false) {
            throw new NotFoundException;
        }

        if (!Application::$app->user->isAdmin()) {
            throw new ForbiddenException;
        }

        $data = $request->getBody();
        $user = Application::$app->user;

        if (!isset($data['id'])) {
            throw new NotFoundException;
        }

        $vacant = Vacant::findOne(['id' => intval($data['id'])]);

        if ($vacant === false) {
            throw new NotFoundException;
        }

        if (!$user->isAdmin() && $vacant->user_id !== $user->id) {
            throw new ForbiddenException;
        }

        $vacant->loadData($data);

        if ($vacant->validate($data) && $vacant->update($data)) {
            return $vacant->getData();
        }

        return $vacant->formatErrors();
    }

    public function delete(Request $request)
    {
        if ($request->isDelete() === false) return;

        if (!Application::$app->user->isAdmin()) {
            throw new ForbiddenException;
        }

        $data = $request->getBody();
        $user = Application::$app->user;

        if (!isset($data['id'])) {
            return "You must specify id of the shift";
        }

        $vacant = Vacant::findOne(['id' => intval($data['id'])]);

        if ($vacant === false) {
            throw new NotFoundException;
        }

        if (!$user->isAdmin() && $vacant->user_id !== $user->id) {
            throw new ForbiddenException;
        }

        if ($vacant->delete()) {
            return true;
        }

        return $vacant->formatErrors();
    }
}
