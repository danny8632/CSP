<?php

declare(strict_types=1);

namespace app\models;

use app\core\db\DbModel;
use DateTime;

class SealedShift extends DbModel
{
    public int $id              = 0;
    public int $shift_id        = 0;
    public ?DateTime $orig_from = null;
    public ?DateTime $orig_to   = null;


    public function tableName(): string
    {
        return 'sealedShifts';
    }

    public function primaryKey(): string
    {
        return 'id';
    }

    public function rules(): array
    {
        return [
            'shift_id'  => [self::RULE_REQUIRED, self::RULE_INT],
            'orig_from' => [self::RULE_DATETIME],
            'orig_to'   => [self::RULE_DATETIME]
        ];
    }

    public function attributes(): array
    {
        return ['shift_id', 'orig_from', 'orig_to'];
    }

    public function properties(): array
    {
        return ['id', 'shift_id', 'orig_from', 'orig_to'];
    }
}