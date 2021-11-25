<?php

namespace app\core\db;

use app\core\Model;
use app\core\Application;
use app\core\exception\NotFoundException;

abstract class DbModel extends Model
{
    private static array $parser = [
        'boolean' => FILTER_VALIDATE_BOOLEAN,
        'integer' => FILTER_VALIDATE_INT,
        'double'  => FILTER_VALIDATE_FLOAT,
        'float'   => FILTER_VALIDATE_FLOAT
    ];

    abstract public function tableName(): string;
    abstract public function attributes(): array;
    abstract public function primaryKey(): string;

    public function save()
    {
        $tableName  = $this->tableName();
        $attributes = $this->attributes();

        $params = implode(',', array_map(fn ($attr) => ":$attr", $attributes));
        $attributesSql = implode(',', $attributes);

        $statement = self::prepare("INSERT INTO $tableName ($attributesSql) VALUES ($params);");

        foreach ($attributes as $attribute) {
            $statement->bindValue(":$attribute", $this->{$attribute});
        }

        $statement->execute();
        return true;
    }


    public function update()
    {
        $tableName  = $this->tableName();
        $attributes = $this->attributes();
        $primaryKey = $this->primaryKey();

        unset($attributes[$this->primaryKey()]);

        $params = implode(',', array_map(fn ($attr) => "`$attr` = :$attr", $attributes));


        $statement = self::prepare("UPDATE $tableName SET $params WHERE $primaryKey = :primaryKey;");

        foreach ($this->attributes() as $attribute) {
            $statement->bindValue(":$attribute", $this->{$attribute});
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

        $sqlWhere = implode("AND ", array_map(fn ($attr) => "$attr = :$attr", $attributes));

        $statement = self::prepare("SELECT * FROM $tableName WHERE $sqlWhere LIMIT 1;");

        foreach ($where as $key => $item) {
            $statement->bindValue(":$key", $item);
        }

        $statement->execute();

        $result = $statement->fetchObject(static::class);

        if ($result === false) {
            throw new NotFoundException;
        }

        return $result;
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

        $response = [];
        foreach ($records as $data) {
            $instance = new static();

            foreach ($data as $key => $value) {
                if (is_numeric($key)) continue;

                $type = isset($instance->{$key}) ? gettype($instance->{$key}) : null;
                if (in_array($type, self::$parser)) {
                    $value = filter_var($value, self::$parser[$type]);
                }

                $instance->{$key} = $value;
            }
            $response[] = $instance;
        }
        return $response;
    }


    public static function prepare(string $sql): \PDOStatement|false
    {
        return Application::$app->db->pdo->prepare($sql);
    }
}
