<?php

use app\core\Application;

class m0007_DepartmentRelation
{
    public function up()
    {
        $db = Application::$app->db;

        $SQL = "CREATE TABLE DepartmentRelation (
            id INT NOT NULL AUTO_INCREMENT,
            user_id INT NOT NULL,
            department_id INT NOT NULL,
            created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            modified TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            INDEX (user_id),
            INDEX (department_id),
            FOREIGN KEY (user_id)
                REFERENCES Users(id)
                ON UPDATE NO ACTION
                ON DELETE NO ACTION,
            FOREIGN KEY (department_id)
                REFERENCES Departments(id)
                ON UPDATE NO ACTION
                ON DELETE NO ACTION
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