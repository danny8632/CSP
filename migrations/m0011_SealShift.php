<?php

use app\core\Application;

class m0011_SealShift
{
    public function up()
    {
        $db = Application::$app->db;

        $SQL = "CREATE TABLE sealedShifts (
            id INT NOT NULL AUTO_INCREMENT,
            shift_id INT NOT NULL,
            `orig_from` TIMESTAMP NULL DEFAULT NULL,
            `orig_to` TIMESTAMP NULL DEFAULT NULL,
            created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            modified TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            INDEX (shift_id),
            FOREIGN KEY (shift_id)
                REFERENCES Shifts(id)
                ON UPDATE NO ACTION
                ON DELETE CASCADE
            ) ENGINE = InnoDB;";
        $db->pdo->exec($SQL);
    }

    public function down()
    {
        $db = Application::$app->db;
        $SQL = "DROP TABLE sealedShifts;";
        $db->pdo->exec($SQL);
    }
}