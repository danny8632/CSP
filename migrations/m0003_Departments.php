<?php

use app\core\Application;

class m0003_Departments
{
    public function up()
    {
        $db = Application::$app->db;

        $SQL = "CREATE TABLE Departments (
            id INT NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            modified TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
            ) ENGINE = InnoDB;";
        $db->pdo->exec($SQL);
    }

    public function down()
    {
        $db = Application::$app->db;
        $SQL = "DROP TABLE RefreshToken;";
        $db->pdo->exec($SQL);
    }
}