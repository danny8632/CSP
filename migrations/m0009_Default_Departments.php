<?php

use app\core\Application;

class m0008_Default_Users
{
    public function up()
    {
        $db = Application::$app->db;

        $SQL = "INSERT INTO Departments
                (name)
            VALUES
                ('CEO'),
                ('COO'),
                ('Marketing'),
                ('Opvasker'),
                ('Køkken'),
                ('Tjener'),
                ('Lager'),
                ('Security'),
                ('Forsker'),
                ('Rengøring'),
                ('Support')
        ;";
        $db->pdo->exec($SQL);
    }

    public function down()
    {
        $db = Application::$app->db;
        $SQL = "DELETE FROM Departments WHERE name IN ('CEO', 'COO', 'Marketing', 'Opvasker', 'Køkken', 'Tjener', 'Lager', 'Security', 'Forsker', 'Rengøring', 'Support');";
        $db->pdo->exec($SQL);
    }
}