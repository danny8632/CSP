<?php

declare(strict_types=1);

namespace app\controllers;

use app\core\Application;
use app\core\Controller;
use app\core\exception\ForbiddenException;
use app\core\exception\NotFoundException;
use app\core\middlewares\AuthMiddleware;
use app\core\Request;
use app\models\SealedShift;
use app\models\Shift;

class SealedShiftsController extends Controller
{
    public function __construct()
    {
        $this->registerMiddleware(new AuthMiddleware(['get', 'post', 'delete']));
    }

    public function get(Request $request)
    {
        if ($request->isGet() === false) {
            throw new NotFoundException;
        }

        $user = Application::$app->user;
        $data = $request->getBody();

        if (isset($data['shift_id'])) {
            return SealedShift::findAll([['shift_id', '=', $data['shift_id']]]);
        }

        $shifts = [];
        if($user->isAdmin()) {
            $shifts = Shift::findAll([['1', '=', '1']]);
        } else {
            $shifts = Shift::findAll([['user_id', '=', $user->id]]);
        }

        $shiftIds = array_map(fn ($shift) => $shift['id'], $shifts);

        return SealedShift::findAll([
            ['shift_id', 'IN', '(' . implode(',', $shiftIds) . ')']
        ]);
    }

    public function post(Request $request)
    {
        if ($request->isPost() === false) {
            throw new NotFoundException;
        }

        $user = Application::$app->user;

        if(!$user->isAdmin()) {
            throw new ForbiddenException;
        }

        $data = $request->getBody();

        $shift_id = intval($data['shift_id']);
        $sealedShift = new SealedShift();

        if(count(SealedShift::findAll([[ 'shift_id', '=', $shift_id ]])) > 0) {
            throw new \Exception("This seal already exists", 403);
        }

        $shift = Shift::findOne(['id' => $shift_id]);

        $shiftData = $shift->getData();
        $sealedShift->loadData([
            'orig_from' => $shiftData['from'],
            'orig_to'   => $shiftData['to'],
            'shift_id' => $shift_id
        ]);

        $newShiftData = [
            'from' => $data['from'] ?? $shiftData['from'],
            'to' => $data['to'] ?? $shiftData['to'],
        ];
        $shift->loadData($newShiftData);

        if($shift->validate($newShiftData) && $shift->update($newShiftData))
        {
            if ($sealedShift->validate() && $sealedShift->save()) {
                return $sealedShift->getData();
            }

            return $sealedShift->formatErrors();
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
            throw new \Exception("Id missing", 403);
        }

        $sealedShift = SealedShift::findOne(['id' => intval($data['id'])]);
        $shift = Shift::findOne(['id' => $sealedShift->shift_id]);

        $sealedShiftData = $sealedShift->getData();
        $newShiftData = [
            'from' => $sealedShiftData['orig_from'],
            'to' => $sealedShiftData['orig_to']
        ];

        $shift->loadData($newShiftData);
        if($shift->validate($newShiftData) && $shift->update($newShiftData))
        {
            $sealedShift->delete();
            return $shift->getData();
        }
        return $shift->formatErrors();
    }
}
