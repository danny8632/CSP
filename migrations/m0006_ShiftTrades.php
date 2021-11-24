<?php

use app\core\Application;

class m0006_ShiftTrades
{
    public function up()
    {
        $db = Application::$app->db;

        $SQL = "CREATE TABLE ShiftTrades (
            id INT NOT NULL AUTO_INCREMENT,
            currentowner INT NOT NULL,
            newowner INT NOT NULL,
            shift_id INT NOT NULL,
            created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            modified TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            INDEX (currentowner),
            INDEX (newowner),
            FOREIGN KEY (currentowner)
                REFERENCES Users(id)
                ON UPDATE NO ACTION
                ON DELETE CASCADE,
            FOREIGN KEY (newowner)
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