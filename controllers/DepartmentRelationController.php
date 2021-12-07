<?php

declare(strict_types=1);

namespace app\controllers;

use app\core\Application;
use app\core\Controller;
use app\core\exception\ForbiddenException;
use app\core\exception\NotFoundException;
use app\core\middlewares\AuthMiddleware;
use app\core\Request;
use app\models\DepartmentRelation;

class DepartmentRelationController extends Controller
{
    public function __construct()
    {
        $this->registerMiddleware(new AuthMiddleware(['get', 'post', 'delete']));
    }


    public function get(Request $request)
    {
        $data = $request->getBody();
        $userId = intval($data['user_id'] ?? Application::$app->user->id);

        //  security
        if (!Application::$app->user->isAdmin() && $userId !== Application::$app->user->id) {
            throw new ForbiddenException;
        }

        return DepartmentRelation::findAll([['user_id', '=', $userId]]);
    }


    public function post(Request $request)
    {
        if ($request->isPost() === false) return;

        //  security
        if (!Application::$app->user->isAdmin()) {
            throw new ForbiddenException;
        }

        $data = $request->getBody();

        if (!isset($data['user_id'])) {
            return json_encode(["Error", "You must parse user_id"]);
        }

        if (isset($data['department_ids'])) {
            $response = [];

            foreach ($data['department_ids'] as $depId) {
                $data['department_id'] = $depId;

                $departmentRelation = new DepartmentRelation();
                $departmentRelation->loadData($data);

                if ($departmentRelation->validate() && $departmentRelation->save()) {
                    $response[] = $departmentRelation->getData();
                } else {
                    return $departmentRelation->formatErrors();
                }
            }

            return $request;
        } else {
            $departmentRelation = new DepartmentRelation();
            $departmentRelation->loadData($request->getBody());

            if ($departmentRelation->validate() && $departmentRelation->save()) {
                return $departmentRelation->getData();
            }

            return $departmentRelation->formatErrors();
        }
    }

    public function delete(Request $request)
    {
        if ($request->isDelete() === false) return;

        //  security
        if (!Application::$app->user->isAdmin()) {
            throw new ForbiddenException;
        }

        $data = $request->getBody();

        if (!isset($data['user_id']) || !isset($data['department_ids'])) {
            return json_encode(["Error", "You must parse user_id and department_ids"]);
        }

        $departmentRelations = DepartmentRelation::findAll([
            [ 'user_id', '=', intval($data['user_id']) ],
            [ 'department_id', 'IN', '(' . implode(',', $data['department_ids']) . ')' ]
        ], false);


        foreach ($departmentRelations as $departmentRelation) {
            $departmentRelation->delete();
        }

        return true;
    }
}
