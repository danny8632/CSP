<?php

use app\core\Application;

class m0002_RefreshToken
{
    public function up()
    {
        $db = Application::$app->db;

        $SQL = "CREATE TABLE RefreshToken (
            id INT NOT NULL AUTO_INCREMENT,
            user_id INT NOT NULL,
            token VARCHAR(255) NOT NULL,
            expire TIMESTAMP NOT NULL,
            created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            modified TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            INDEX (user_id),
            FOREIGN KEY (user_id)
                REFERENCES Users(id)
                ON UPDATE NO ACTION
                ON DELETE CASCADE
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