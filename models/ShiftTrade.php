<?php

declare(strict_types=1);

namespace app\models;

use app\core\db\DbModel;

class ShiftTrade extends DbModel
{
    public int $id           = 0;
    public int $currentowner = 0;
    public int $newowner     = 0;
    public int $shift_id     = 0;


    public function tableName(): string
    {
        return 'ShiftTrades';
    }

    public function primaryKey(): string
    {
        return 'id';
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
        return ['id', 'currentowner', 'newowner', 'shift_id'];
    }
}