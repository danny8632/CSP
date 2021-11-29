<?php

use app\core\Application;

class m0005_Vacant
{
    public function up()
    {
        $db = Application::$app->db;

        $SQL = "CREATE TABLE Vacant (
            id INT NOT NULL AUTO_INCREMENT,
            user_id INT NOT NULL,
            type ENUM('vacant_wish','vacation_wish', 'vacant', 'vacation') NOT NULL,
            `from` TIMESTAMP NOT NULL DEFAULT '0000-00-00',
            `to` TIMESTAMP NOT NULL DEFAULT '0000-00-00',
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
        $SQL = "DROP TABLE Shifts;";
        $db->pdo->exec($SQL);
    }
}