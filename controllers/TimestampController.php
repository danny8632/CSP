<?php

declare(strict_types=1);

namespace app\controllers;

use app\core\Application;
use app\core\Controller;
use app\core\exception\ForbiddenException;
use app\core\exception\NotFoundException;
use app\core\middlewares\AuthMiddleware;
use app\core\Request;
use app\models\Timestamp;

class TimestampController extends Controller
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

        
    }

    public function post(Request $request)
    {
        if ($request->isPost() === false) {
            throw new NotFoundException;
        }

        $timestamp = new Timestamp();
        $timestamp->loadData($request->getBody());

        if ($timestamp->validate($request->getBody()) && $timestamp->save()) {
            return $timestamp->getData();
        }

        return $timestamp->formatErrors();
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
        $timestamp = Timestamp::findOne(['shift_id' => intval($data('shift_id'))]);

        if ($timestamp === false) {
            throw new NotFoundException;
        }

        $timestamp->loadData($data);

        if ($timestamp->validate() && $timestamp->save()) {
            return $timestamp->getData();
        }

        return $timestamp->formatErrors();
    }

    public function delete(Request $request)
    {
        if ($request->isDelete() === false) {
            throw new NotFoundException;
        }

        if (!Application::$user->isAdmin()) {
            throw new ForbiddenException;
        }

        $data = $request->getBody();

        if (!isset($data['shift_id'])) {
            return "You must specify the id of the shift.";
        }

        $timestamp = Timestamp::findOne(['shift_id' => intval($data['shift_id'])]);

        if ($timestamp === false) {
            throw new NotFoundException;
        }

        if ($timestamp->delete()) {
            return true;
        }

        $timestamp->formatErrors();
    }
}