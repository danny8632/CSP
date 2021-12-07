<?php

declare(strict_types=1);

namespace app\controllers;

use app\core\Application;
use app\core\Controller;
use app\core\exception\ForbiddenException;
use app\core\exception\NotFoundException;
use app\core\middlewares\AuthMiddleware;
use app\core\Request;
use app\models\Shift;
use app\models\DepartmentRelation;

class ShiftController extends Controller
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
            $shift = null;

            if ($user->isAdmin()) {
                $shift = Shift::findOne(['id' => $id])->getData();
            } else {
                $shift = Shift::findOne(['id' => $id, "user_id" => $user->id])->getData();
            }

            return $shift;
        }

        if (isset($data['user_id'])) {
            $user_id = intval($data['user_id']);
            
            if (!$user->isAdmin() && $user_id !== $user->id) {
                throw new ForbiddenException;
            }

            return Shift::findAll([['user_id', '=', $user_id]]);
        }

        if (isset($data['department_id'])) {
            $department_id = intval($data['department_id']);

            if (!$user->isAdmin()) {
                $relations = DepartmentRelation::findAll([
                    ['user_id', '=', $user->id],
                    ['department_id', '=', $department_id]
                ]);
    
                if (count($relations) === 0) {
                    throw new ForbiddenException;
                }
            }

            return Shift::findAll([['department_id', '=', $department_id]]);
        }

        if ($user->isAdmin() === false) {
            throw new ForbiddenException;
        }

        return Shift::findAll([['1', '=', '1']]);
    }

    public function post(Request $request)
    {
        if ($request->isPost() === false) {
            throw new NotFoundException;
        }

        if (!Application::$app->user->isAdmin()) {
            throw new ForbiddenException;
        }

        $shift = new Shift();
        $shift->loadData($request->getBody());

        if ($shift->validate() && $shift->save()) {
            return $shift->getData();
        }

        return $shift->formatErrors();
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

        if (!isset($data['id'])) {
            throw new NotFoundException;
        }

        $shift = Shift::findOne(['id' => intval($data['id'])]);

        if ($shift === false) {
            throw new NotFoundException;
        }

        $shift->loadData($data);

        if ($shift->validate($data) && $shift->update($data)) {
            return $shift->getData();
        }

        return $shift->formatErrors();
    }

    public function delete(Request $request)
    {
        if ($request->isDelete() === false) return;

        if (!Application::$app->user->isAdmin()) {
            throw new ForbiddenException;
        }

        $data = $request->getBody();

        if (!isset($data['id'])) {
            return "You must specify id of the shift";
        }

        $shift = Shift::findOne(['id' => intval($data['id'])]);

        if ($shift === false) {
            throw new NotFoundException;
        }

        if ($shift->delete()) {
            return true;
        }

        return $shift->formatErrors();
    }
}