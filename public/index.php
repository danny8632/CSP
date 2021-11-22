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



use app\controllers\SiteController;
use app\controllers\AuthController;
use app\core\Application;

require_once __DIR__.'/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();


$config = [
    'userClass' => \app\models\User::class,
    'db' => [
        'dsn'      => $_ENV['DB_DSN'],
        'user'     => $_ENV['DB_USER'],
        'password' => $_ENV['DB_PASSWORD'],
    ],
    'auth_key' => $_ENV['AUTH_KEY']
];


$app = new Application(dirname(__DIR__), $config);


$app->router->get('/test', function() {
    return "test";
});


//$app->router->get('/hej', [SiteController::class, 'home']);

/* $app->router->get('/', [SiteController::class, 'home']);
$app->router->get('/contact', [SiteController::class, 'contact']);
$app->router->post('/contact', [SiteController::class, 'contact']);

$app->router->get('/login', [AuthController::class, 'login']);

$app->router->get('/register', [AuthController::class, 'register']);

$app->router->get('/logout', [AuthController::class, 'logout']);
$app->router->get('/profile', [AuthController::class, 'profile']);
$app->router->get('/user', [AuthController::class, 'user']);
 */

$app->router->post('/login', [AuthController::class, 'login']);
$app->router->post('/register', [AuthController::class, 'register']);
$app->router->get('/user', [AuthController::class, 'user']);

$app->run();