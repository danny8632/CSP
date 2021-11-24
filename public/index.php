<?php

declare(strict_types=1);

/**
 * This sections is to force the use of the frontend
 * but only if the uri does not contain api
 */
$serverRequestPath = $_SERVER['REQUEST_URI'] ?? '/';
$isApiCall = strpos($serverRequestPath, 'api');

if($isApiCall === false) {
    ob_start();
    include_once "./index.html";
    echo ob_get_clean();
    return;
}



use app\controllers\AuthController;
use app\controllers\DepartmentController;
use app\controllers\DepartmentRelationController;
use app\core\Application;

require_once __DIR__.'/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();


$config = [
    'userClass'  => \app\models\User::class,
    'token_salt' => $_ENV['TOKEN_SALT'],
    'db' => [
        'dsn'      => $_ENV['DB_DSN'],
        'user'     => $_ENV['DB_USER'],
        'password' => $_ENV['DB_PASSWORD'],
    ],
    'auth_key' => $_ENV['AUTH_KEY']
];


$app = new Application(dirname(__DIR__), $config);

$app->router->post('/login', [AuthController::class, 'login']);
$app->router->post('/register', [AuthController::class, 'register']);
$app->router->post('/tokenRefresh', [AuthController::class, 'tokenRefresh']);

$app->router->get('/departmentRelation', [DepartmentRelationController::class, 'get']);
$app->router->post('/departmentRelation', [DepartmentRelationController::class, 'post']);
$app->router->delete('/departmentRelation', [DepartmentRelationController::class, 'delete']);

$app->router->get('/department', [DepartmentController::class, 'get']);
$app->router->post('/department', [DepartmentController::class, 'post']);
$app->router->delete('/department', [DepartmentController::class, 'delete']);


$app->run();