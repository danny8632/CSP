<?php

declare(strict_types=1);

namespace app\models;

use app\core\db\DbModel;
use app\core\exception\NotFoundException;
use DateTime;
use Error;

class Vacant extends DbModel
{
    const TYPES = ['vacant_wish','vacation_wish','vacant','vacation'];

    public int $id           = 0;
    public int $user_id      = 0;
    public string $type;
    public DateTime $from;
    public DateTime $to;


    public function tableName(): string
    {
        return 'Vacant';
    }

    public function primaryKey(): string
    {
        return 'id';
    }

    public function validate(?array $data = null): bool
    {
        if ($data !== null && isset($data['type']) && !in_array($data['type'], self::TYPES)) {
            $this->addError("type", "Type must be one of: ". json_encode(self::TYPES));
        } else if (!in_array($this->type, self::TYPES)) {
            $this->addError("type", "Type must be one of: ". json_encode(self::TYPES));
        }

        return parent::validate($data);
    }

    public function rules(): array
    {
        return [
            'user_id' => [self::RULE_REQUIRED, self::RULE_INT],
            'type' => [self::RULE_REQUIRED],
            'from' => [self::RULE_REQUIRED, self::RULE_DATETIME],
            'to' => [self::RULE_REQUIRED, self::RULE_DATETIME]
        ];
    }

    public function attributes(): array
    {
        return ['user_id', 'type', 'from', 'to'];
    }

    public function properties(): array
    {
        return ['id', 'user_id', 'type', 'from', 'to'];
    }
}