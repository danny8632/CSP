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

class ShiftController extends Controller
{
    public function __construct()
    {
        $this->registerMiddleware(new AuthMiddleware(['get', 'post', 'put', 'delete']));
    }

    public function get(Request $request)
    {
        
    }

    public function post(Request $request)
    {

    }

    public function put(Request $request)
    {

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