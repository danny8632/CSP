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
use app\models\SealedShift;
use app\models\Shift;
use app\models\User;

class HourReportController extends Controller
{
    public function __construct()
    {
        $this->registerMiddleware(new AuthMiddleware(['post']));
    }

    public function post(Request $request)
    {
        if ($request->isPost() === false) {
            throw new NotFoundException;
        }

        if(!Application::$app->user->isAdmin()) {
            throw new ForbiddenException;
        }

        $data = $request->getBody();

        if(!isset($data['from']) || !isset($data['to']) || !isset($data['departments'])) {
            throw new \Exception("Missing from, to or departments", 403);
        }

        $depIds = '(\'' . implode('\',\'', array_map('intval', $data['departments'])) . '\')';

        $shifts = Shift::findAll([
            ['`from`', '>', '\''.$data['from'].'\''],
            ['`to`', '<', '\''.$data['to'].'\''],
            ['department_id', 'IN', $depIds]
        ]);

        $departmentRelations = DepartmentRelation::findAll([['department_id', 'IN', $depIds]]);
        $users = User::findAll([
            ['id', 'IN', '(\'' . implode('\',\'', array_map(fn($dr) => intval($dr['department_id']), $departmentRelations)) . '\')']
        ]);

        $sealedShifts = SealedShift::findAll([['1', '=', '1']]);

        foreach ($users as $key => $user) {
            $users[$key]['shifts'] = [];

            foreach ($shifts as $key => $shift) {
                if($shift['user_id'] !== $user['id']) continue;
                $shift['sealed'] = array_filter($sealedShifts, fn($ss) => $ss['shift_id'] === $shift['id'])[0] ?? null;
                $users[$key]['shifts'][] = $shift;
            }
        }

        return $users;
    }
}
