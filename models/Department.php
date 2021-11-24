<?php

declare(strict_types=1);

namespace app\models;

use app\core\db\DbModel;

class Department extends DbModel
{
    public int    $id   = -1;
    public string $name = '';

    public function tableName(): string
    {
        return 'Departments';
    }

    public function primaryKey(): string
    {
        return 'id';
    }

    public function rules(): array
    {
        return [
            'id'   => [self::RULE_INT],
            'name' => [self::RULE_REQUIRED]
        ];
    }

    public function attributes(): array
    {
        return ['name'];
    }


    public function properties(): array
    {
        return ['id', 'name'];
    }


    public function labels(): array
    {
        return [
            'name' => 'Name',
        ];
    }
}
