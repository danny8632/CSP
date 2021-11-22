<?php

namespace app\core\db;

use app\core\Model;
use app\core\Application;

abstract class DbModel extends Model
{
    abstract public function tableName(): string;
    abstract public function attributes(): array;
    abstract public function primaryKey(): string;


    public function save()
    {
        $tableName = $this->tableName();
        $attributes = $this->attributes();

        $params = implode(',', array_map(fn($attr) => ":$attr", $attributes));
        $attributesSql = implode(',', $attributes);

        $statement = self::prepare("INSERT INTO $tableName ($attributesSql) VALUES ($params);");

        foreach ($attributes as $attribute)
        {
            $statement->bindValue(":$attribute", $this->{$attribute});
        }

        $statement->execute();
        return true;
    }


    public static function findOne($where)
    {
        $tableName  = (new static)->tableName();
        $attributes = array_keys($where);

        $sqlWhere = implode("AND ", array_map(fn($attr) => "$attr = :$attr", $attributes));
        
        $statement = self::prepare("SELECT * FROM $tableName WHERE $sqlWhere;");
        
        foreach ($where as $key => $item)
        {
            $statement->bindValue(":$key", $item);
        }

        $statement->execute();
        return $statement->fetchObject(static::class);
    }


    public static function prepare(string $sql) : \PDOStatement|false
    {
        return Application::$app->db->pdo->prepare($sql);
    }

}