<?php

namespace app\core\db;

use app\core\Model;
use app\core\Application;
use app\core\exception\NotFoundException;
use DateTime;

abstract class DbModel extends Model
{
    private static array $parser = [
        'boolean' => FILTER_VALIDATE_BOOLEAN,
        'integer' => FILTER_VALIDATE_INT,
        'double'  => FILTER_VALIDATE_FLOAT,
        'float'   => FILTER_VALIDATE_FLOAT
    ];

    private static array $insertParams = [
        'boolean' => \PDO::PARAM_BOOL,
        'integer' => \PDO::PARAM_INT,
        'double'  => \PDO::PARAM_STR,
        'float'   => \PDO::PARAM_STR
    ];

    abstract public function tableName(): string;
    abstract public function attributes(): array;
    abstract public function primaryKey(): string;

    public function save()
    {
        $tableName  = $this->tableName();
        $attributes = $this->attributes();

        $params = implode(',', array_map(fn ($attr) => ":$attr", $attributes));
        $attributesSql = implode('`,`', $attributes);

        $statement = self::prepare("INSERT INTO $tableName (`$attributesSql`) VALUES ($params);");


        foreach ($attributes as $attribute) {
            $value = $this->{$attribute};

            $type      = isset($this->{$attribute}) ? gettype($this->{$attribute}) : null;
            $paramType = \PDO::PARAM_STR;;
            if (isset(self::$insertParams[$type])) {
                $paramType = self::$insertParams[$type];
            }


            if (is_a($this->{$attribute}, DateTime::class)) {
                $statement->bindValue(":$attribute", $value->format(self::TIMESTAMP_FORMAT), \PDO::PARAM_STR);
            } else {
                $statement->bindValue(":$attribute", $value, $paramType);
            }
        }

        return $statement->execute();
    }


    public function update(?array $data = null)
    {
        $tableName  = $this->tableName();
        $insertAttributes = $data !== null ? array_keys($data) : $this->attributes();
        $attributes = $data !== null ? array_keys($data) : $this->attributes();
        $primaryKey = $this->primaryKey();

        unset($insertAttributes[$this->primaryKey()]);

        $params = implode(',', array_map(fn ($attr) => "`$attr` = :$attr", $insertAttributes));


        $statement = self::prepare("UPDATE $tableName SET $params WHERE $primaryKey = :$primaryKey;");

        foreach ($attributes as $attribute) {
            $value = $this->{$attribute};

            if (is_a($this->{$attribute}, DateTime::class)) {
                $value = $value->format(self::TIMESTAMP_FORMAT);
            }

            if(is_bool($this->{$attribute})) {
                $value = intval($value);
            }

            $statement->bindValue(":$attribute", $value);
        }

        $statement->execute();
        return true;
    }


    public function delete()
    {
        $tableName  = $this->tableName();
        $primaryKey = $this->primaryKey();
        $primaryValue = $this->{$primaryKey};

        $statement = self::prepare("DELETE FROM $tableName WHERE $primaryKey = $primaryValue;");
        $statement->execute();
        return true;
    }


    public static function findOne($where): DbModel
    {
        $tableName  = (new static)->tableName();
        $attributes = array_keys($where);

        $sqlWhere = implode("AND ", array_map(fn ($attr) => "`$attr` = :$attr", $attributes));

        $statement = self::prepare("SELECT * FROM $tableName WHERE $sqlWhere LIMIT 1;");

        foreach ($where as $key => $item) {
            $statement->bindValue(":$key", $item);
        }

        $statement->execute();

        $result = $statement->fetchAll();

        if ($result === false) {
            throw new NotFoundException;
        }

        return self::parseDbData($result, false)[0];
    }


    public static function findAll(array $where = []): array
    {
        $tableName  = (new static)->tableName();

        $whereStatement = [];
        foreach ($where as $section) {
            $whereStatement[] = $section[0] . ' ' . $section[1] . ' ' . $section[2];
        }
        $whereStatement = implode("AND ", $whereStatement);

        $statement = self::prepare("SELECT * FROM $tableName WHERE $whereStatement;");
        $statement->execute();
        $records = $statement->fetchAll();

        return self::parseDbData($records);
    }


    private static function parseDbData(array $records, bool $format = true)
    {
        $response = [];
        foreach ($records as $data) {
            $instance = new static();

            foreach ($data as $key => $value) {
                if (is_numeric($key)) continue;

                $type = isset($instance->{$key}) ? gettype($instance->{$key}) : null;
                if (in_array($type, self::$parser)) {
                    $value = filter_var($value, self::$parser[$type]);
                }

                if (isset($instance->{$key}) && is_a($instance->{$key}, DateTime::class)) {
                    $value = DateTime::createFromFormat(self::TIMESTAMP_FORMAT, $value);
                }

                $instance->{$key} = $value;
            }
            $response[] = $format ? $instance->getData() : $instance;
        }
        return $response;
    }


    public static function prepare(string $sql): \PDOStatement|false
    {
        return Application::$app->db->pdo->prepare($sql);
    }
}
