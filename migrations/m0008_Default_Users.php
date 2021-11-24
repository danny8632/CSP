<?php

use app\core\Application;

class m0008_Default_Users
{
    public function up()
    {
        $db = Application::$app->db;

        $SQL = "INSERT INTO Users
            (username, password, firstname, lastname, type, requiredhours, monthlypay)
            VALUES
            ('danny8632', '$2y$10\$pipR2Qea3ByoqGJU.Syy9uD7id6VxN0fvJwxZ5hSXD6CXCus7IIs2', 'Danny', 'Haslund', 'admin', '37', '1'),
            ('admin', '$2y$10\$i3HxG2F5XZUj6KxOWBkNS.7uaYD9bnDI12KuzcIgdnjfQJgkmIHti', 'Admin', 'Super', 'admin', '37', '1'),
            ('medarbejder1', '$2y$10\$BmCHvIDlCvhnD1ws7sxw8eRZqXCPjaVoa8rFamN3iY.xXgTJAEj0O', 'Medarbejder', 'Månedslønnet', 'employee', '37', '1'),
            ('medarbejder2', '$2y$10$2bhyGZyjRdTESN/E8PYwTeIYwMEwFjWjtDgqletpGLEUki5a85RI2', 'Medarbejder', 'Timelønnet', 'employee', '0', '0')
        ;";
        $db->pdo->exec($SQL);
    }

    public function down()
    {
        $db = Application::$app->db;
        $SQL = "DELETE FROM Users WHERE username IN ('danny8632', 'admin', 'medarbejder1', 'medarbejder2');";
        $db->pdo->exec($SQL);
    }
}