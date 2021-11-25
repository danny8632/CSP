<?php

declare(strict_types=1);

namespace app\controllers;

use app\core\Application;
use app\core\Controller;
use app\core\exception\ForbiddenException;
use app\core\exception\NotFoundException;
use app\core\middlewares\AuthMiddleware;
use app\core\Request;
use app\models\Department;
use app\models\DepartmentRelation;

class DepartmentController extends Controller
{
    public function __construct()
    {
        $this->registerMiddleware(new AuthMiddleware(['get', 'post', 'put', 'delete']));
    }


    public function get(Request $request)
    {
        $data = $request->getBody();
        $user = Application::$app->user;
        $isAdmin = $user->isAdmin();

        if (isset($data['id']) || isset($data['department_id'])) {
            $id = isset($data['id']) ? intval($data['id']) : (isset($data['department_id']) ? intval($data['department_id']) : 0);

            if ($isAdmin === false) {
                $relations = DepartmentRelation::findAll([
                    ['user_id', '=', $user->id],
                    ['department_id', '=', $id]
                ]);

                if (count($relations) === 0) {
                    throw new ForbiddenException;
                }
            }
            return Department::findOne(['id' => $id]);
        }

        if (isset($data['user_id'])) {
            $userId = intval($data['user_id']);

            if ($isAdmin === false && $userId !== $user->id) {
                throw new ForbiddenException;
            }

            $relations = DepartmentRelation::findAll([['user_id', '=', $userId]]);

            if (count($relations) === 0) {
                return [];
            }

            return Department::findAll([['id', 'IN', '(' . implode(',', array_map(fn ($realtion) => $realtion->department_id, $relations)) . ')']]);
        }

        if ($isAdmin === false) {
            throw new NotFoundException;
        }

        return Department::findAll([['1', '=', '1']]);
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

        $department = new Department();
        $department->loadData($request->getBody());

        if ($department->validate() && $department->save()) {
            return $department;
        }

        return $department->formatErrors();
    }


    public function put(Request $request)
    {
        if ($request->isPut() === false) {
            throw new NotFoundException;
        }

        //  security
        if (!Application::$app->user->isAdmin()) {
            throw new ForbiddenException;
        }

        $data = $request->getBody();

        if (!isset($data['id'])) {
            throw new NotFoundException;
        }

        $department = Department::findOne(['id' => intval($data['id'])]);

        if ($department === false) {
            throw new NotFoundException;
        }

        $department->loadData($data);

        if ($department->validate() && $department->update()) {
            return $department->getData();
        }

        return $department->formatErrors();
    }


    public function delete(Request $request)
    {
        if ($request->isDelete() === false) {
            throw new NotFoundException;
        }

        //  security
        if (!Application::$app->user->isAdmin()) {
            throw new ForbiddenException;
        }

        $data = $request->getBody();

        if (!isset($data['id'])) {
            return "You must parse the id of the relation";
        }

        $department = Department::findOne(['id' => intval($data['id'])]);

        if ($department === false) {
            throw new NotFoundException;
        }

        if ($department->delete()) {
            return true;
        }

        return $department->formatErrors();
    }
}
