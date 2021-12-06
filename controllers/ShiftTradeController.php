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
use app\models\Shift;
use app\models\ShiftTrade;
use app\models\User;

class ShiftTradeController extends Controller
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

        $user = Application::$app->user;

        if (isset($data['id'])) {
            $id = intval($data['id']);

            $ShiftTrade = ShiftTrade::findOne(['id' => $id]);

            if (!$user->isAdmin() && ($ShiftTrade->currentowner !== $user->id || $ShiftTrade->newowner !== $user->id)) {
                throw new ForbiddenException;
            }

            return $ShiftTrade->getData();
        }


        if (isset($data['shift_id'])) {
            $shiftId = intval($data['shift_id']);

            $ShiftTrade = ShiftTrade::findOne(['shift_id' => $shiftId]);

            if (!$user->isAdmin() && ($ShiftTrade->currentowner !== $user->id || $ShiftTrade->newowner !== $user->id)) {
                throw new ForbiddenException;
            }

            return $ShiftTrade->getData();
        }

        if ($user->isAdmin()) {
            return ShiftTrade::findAll([['1', '=', '1']]);
        }

        return array_merge(
            [],
            ShiftTrade::findAll([['currentowner', '=', $user->id]]),
            ShiftTrade::findAll([['newowner', '=', $user->id]])
        );;
    }

    public function post(Request $request)
    {
        if ($request->isPost() === false) {
            throw new NotFoundException;
        }

        $ShiftTrade = new ShiftTrade();
        $ShiftTrade->loadData($request->getBody());

        // Validates that the user is in the department
        $shift = Shift::findOne(['id' => $ShiftTrade->shift_id]);
        $departmentId = $shift->department_id;

        $newUser = DepartmentRelation::findAll([['user_id', '=', $ShiftTrade->newowner], ['department_id', '=', $departmentId]]);

        if (count($newUser) === 0) {
            $ShiftTrade->addError('newowner', "The new owner is not part of the department the shift is in");
        }

        if ($ShiftTrade->validate() && $ShiftTrade->save()) {
            return $ShiftTrade->getData();
        }

        return $ShiftTrade->formatErrors();
    }

    public function delete(Request $request)
    {
        if ($request->isDelete() === false) return;

        $data = $request->getBody();
        $user = Application::$app->user;

        if (!isset($data['id'])) {
            return "You must specify id of the trade";
        }

        $ShiftTrade = ShiftTrade::findOne(['id' => intval($data['id'])]);


        if (!$user->isAdmin() || $ShiftTrade->currentowner !== $user->id) {
            throw new ForbiddenException;
        }

        return $ShiftTrade->delete();
    }

    public function acceptTrade(Request $request)
    {
        $data = $request->getBody();

        if (!isset($data['id'])) {
            return ['Error' => "You must pass an id of the trade"];
        }

        $ShiftTrade = ShiftTrade::findOne(['id' => intval($data['id'])]);

        if ($ShiftTrade->newowner !== Application::$app->user->id) {
            return ['Error' => "You can only accept your own pending shift trade"];
        }

        $shift = Shift::findOne(['id' => $ShiftTrade->shift_id]);

        $newData = ['user_id' => Application::$app->user->id];

        if ($shift->validate($newData) && $shift->update($newData)) {
            return $shift->getData();
        }

        return $shift->formatErrors();
    }

    public function declineTrade(Request $request)
    {
        $data = $request->getBody();

        if (!isset($data['id'])) {
            return ['Error' => "You must pass an id of the trade"];
        }

        $ShiftTrade = ShiftTrade::findOne(['id' => intval($data['id'])]);

        if ($ShiftTrade->newowner !== Application::$app->user->id) {
            return ['Error' => "You can only decline your own pending shift trade"];
        }

        return $ShiftTrade->delete();
    }

    public function findPeople(Request $request)
    {
        $data = $request->getBody();

        if(!isset($data['shift_id'])) {
            return ['Error' => "shift_id is missing"];
        }

        $shift = Shift::findOne(['id' => intval($data['shift_id'])]);

        if($shift->user_id !== Application::$app->user->id) {
            return ['Error' => "You can only trade your own shifts"];
        }

        $relations = departmentRelation::findAll([[ 'department_id', '=', $shift->department_id ]]);

        $userIds = implode(',', array_map(fn($u) => $u['user_id'], $relations));

        return User::findAll(['id', 'IN', "($userIds)" ]);
    }
}
