<?php

use app\core\Application;

class m0001_Users
{
    public function up()
    {
        $db = Application::$app->db;

        $SQL = "CREATE TABLE Users(
            id INT NOT NULL AUTO_INCREMENT,
            username VARCHAR(255) NOT NULL,
            password VARCHAR(255) NOT NULL,
            firstname VARCHAR(255) NOT NULL,
            lastname VARCHAR(255) NOT NULL,
            type ENUM('employee','admin') NOT NULL DEFAULT 'employee',
            requiredhours FLOAT NOT NULL,
            monthlypay BOOLEAN NOT NULL,
            created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            modified TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)) ENGINE = InnoDB;";
        $db->pdo->exec($SQL);
    }

    public function down()
    {
        $db = Application::$app->db;
        $SQL = "DROP TABLE Users;";
        $db->pdo->exec($SQL);
    }
}