<?php

declare(strict_types=1);

namespace app\models;

use app\core\db\DbModel;
use DateTime;

class Shift extends DbModel
{
    public int $id            = 0;
    public int $user_id       = 0;
    public int $department_id = 0;
    public DateTime $from;
    public DateTime $to;
    public int $break_length  = 0;


    public function tableName(): string
    {
        return 'Shifts';
    }

    public function primaryKey(): string
    {
        return 'id';
    }

    public function rules(): array
    {
        return [
            'user_id' => [self::RULE_REQUIRED, self::RULE_INT],
            'department_id' => [self::RULE_REQUIRED, self::RULE_INT],
            'from' => [self::RULE_REQUIRED, self::RULE_DATETIME],
            'to' => [self::RULE_REQUIRED, self::RULE_DATETIME],
            'break_length' => [self::RULE_REQUIRED]
        ];
    }

    public function attributes(): array
    {
        return ['user_id', 'department_id', 'from', 'to', 'break_length'];
    }

    public function properties(): array
    {
        return ['id', 'user_id', 'department_id', 'from', 'to', 'break_length'];
    }
}