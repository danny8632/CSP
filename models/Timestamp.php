<?php

declare(strict_types=1);

namespace app\models;

use app\core\db\DbModel;
use DateTime;

class Timestamp extends DbModel
{
    public int $id            = 0;
    public int $shift_id      = 0;
    public ?DateTime $from;
    public ?DateTime $to;


    public function tableName(): string
    {
        return 'Timestamps';
    }

    public function primaryKey(): string
    {
        return 'id';
    }

    public function rules(): array
    {
        return [
            'shift_id' => [self::RULE_REQUIRED, self::RULE_INT],
            'from' => [self::RULE_DATETIME],
            'to' => [self::RULE_DATETIME]
        ];
    }

    public function attributes(): array
    {
        return ['shift_id', 'from', 'to'];
    }

    public function properties(): array
    {
        return ['id', 'shift_id', 'from', 'to'];
    }
}