<?php

declare(strict_types=1);

namespace app\models;

use app\core\db\DbModel;

class DepartmentRelation extends DbModel
{
    public int $id            = -1;
    public int $user_id       = 0;
    public int $department_id = 0;

    public function tableName(): string
    {
        return 'DepartmentRelation';
    }

    public function primaryKey(): string
    {
        return 'id';
    }

    public function rules(): array
    {
        return [
            'user_id'       => [self::RULE_REQUIRED, self::RULE_INT],
            'department_id' => [self::RULE_REQUIRED, self::RULE_INT]
        ];
    }

    public function attributes(): array
    {
        return ['user_id', 'department_id'];
    }


    public function properties(): array
    {
        return ['id', 'user_id', 'department_id'];
    }


    public function labels(): array
    {
        return [
            'user_id'       => 'UserID',
            'department_id' => 'DepartmentID',
        ];
    }
}
