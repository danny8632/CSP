<?php

declare(strict_types=1);

namespace app\models;

use app\core\db\DbModel;
use app\models\Shift;
use app\models\Department;

class ShiftTrade extends DbModel
{
    public int $id           = 0;
    public int $currentowner = 0;
    public int $newowner     = 0;
    public int $shift_id     = 0;
    public array $shift;
    public array $department;

    public function tableName(): string
    {
        return 'ShiftTrades';
    }

    public function primaryKey(): string
    {
        return 'id';
    }

    public function getData(): array
    {
        $shift = Shift::findOne(['id' => $this->shift_id]);
        $department = Department::findOne(['id' => $shift->department_id]);

        $this->shift = $shift->getData();
        $this->department = $department->getData();

        return parent::getData();
    }

    public function rules(): array
    {
        return [
            'currentowner' => [self::RULE_REQUIRED, self::RULE_INT],
            'newowner' => [self::RULE_REQUIRED, self::RULE_INT],
            'shift_id' => [self::RULE_REQUIRED, self::RULE_INT],
        ];
    }

    public function attributes(): array
    {
        return ['currentowner', 'newowner', 'shift_id'];
    }

    public function properties(): array
    {
        return ['id', 'currentowner', 'newowner', 'shift_id', 'shift', 'department'];
    }
}