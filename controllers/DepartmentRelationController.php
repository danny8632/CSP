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
        $userId = $data['user_id'] ?? Application::$app->user->id;

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

        $departmentRelation = new DepartmentRelation();
        $departmentRelation->loadData($request->getBody());

        if ($departmentRelation->validate() && $departmentRelation->save()) {
            return $departmentRelation;
        }

        return $departmentRelation->formatErrors();
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

        $departmentRelation = DepartmentRelation::findOne(['id' => $data['id']]);

        if ($departmentRelation === false) {
            throw new NotFoundException;
        }

        if ($departmentRelation->delete()) {
            return true;
        }

        return $departmentRelation->formatErrors();
    }
}
