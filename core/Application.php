<?php

declare(strict_types=1);

namespace app\core;

use app\core\db\Database;
use app\models\RefreshToken;
use app\models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;


class Application
{
    /**
     * This is the root directory of the project.
     *
     * @var string
     */
    public static string $ROOT_DIR;

    /**
     * This is the name of the model that is the main usercase.
     * This is used to get the user data that's logged in.
     *
     * @name userClass
     * 
     * @var string
     */
    public string $userClass;

    /**
     * This is the auth key. This is top secret and is set in .env
     *
     * @var string
     */
    private string $authKey = '';

    /**
     * This is the salt for the refresh token. This is top secret and is set in .env
     *
     * @var string
     */
    public string $tokenSalt = '';

    /**
     * This is the chosen algorithms for encrypting the JWT
     */
    private const ALGORITHM = 'HS256';

    /**
     * The router that does all the routes
     *
     * @var Router
     */
    public Router $router;

    /**
     * Request class to get info like method that's used
     * or request data that's parsed from the frontend.
     *
     * @var Request
     */
    public Request $request;

    /**
     * Response class is used to handle stuff like redirect or
     * changing the http code.
     *
     * @var Response
     */
    public Response $response;

    /**
     * Session class handles all getting and setting of different session variables. 
     *
     * @var Session
     */
    public Session $session;

    /**
     * Controller class handles stuff like middleware and helper functions for render.
     *
     * @var Controller|null
     */
    public ?Controller $controller = null;

    /**
     * Database class contains the database connection and migrations logic.
     *
     * @var Database
     */
    public Database $db;

    /**
     * This is the logged in user model. The UserModel is set from the $userClass string.
     *
     * @var UserModel|null
     */
    public ?UserModel $user = null;

    /**
     * This is used to have a static refrence to the Application at all time
     *
     * @var Application
     */
    public static Application $app;


    /**
     * The constructor of the class
     *
     * @param string $rootPath dir path to the root of the "project" something like /var/www/framework
     * @param array $config Should contain "userClass" and "db" 
     */
    public function __construct(string $rootPath, array $config)
    {
        //  Initilizing all class variables to be access later
        $this->userClass = $config['userClass'] ?? '';
        $this->authKey   = $config['auth_key'] ?? '';
        $this->tokenSalt = $config['token_salt'] ?? '';
        self::$ROOT_DIR  = $rootPath;
        self::$app       = $this;

        $this->request  = new Request();
        $this->response = new Response();
        $this->session  = new Session();
        $this->router   = new Router($this->request, $this->response);
        $this->db       = new Database($config['db']);


        //  Finds the actual user from the token
        if ($this->request->getAuthToken() !== false) {
            $this->auth($this->request->getAuthToken());
        }
    }


    /**
     * This function is responsible for calling the router
     * and handle the output. The router will call the function specified in index.php
     * that matches the url and method.
     *
     * @return void
     */
    public function run(): void
    {
        $this->response->setJsonType();

        try {
            echo $this->router->resolve();
        } catch (\Exception $e) {
            $this->response->setStatusCode(intval($e->getCode() !== null ? $e->getCode() : 404));
            echo $e->getMessage();
        }
    }


    /**
     * This function logs the user in and set the users primarykey
     * into the session so it can be fetched on later requests.
     *
     * @param UserModel $user
     * @return array contains jwt and refresh token
     */
    public function login(UserModel $user): array
    {
        $jwt = $this->generateJWT($user);
        $this->user = $user;

        $refreshToken = RefreshToken::new($user->id);

        return [
            'jwt'           => $jwt,
            'refresh_token' => [
                'token' => $refreshToken->token,
                'exp'   => $refreshToken->expire->getTimestamp()
            ]
        ];
    }


    /**
     * Generates the jwt token
     *
     * @param UserModel $user
     * @return array [ 'token' => jwt token, 'exp' => 423589437 ]
     */
    public function generateJWT(UserModel $user): array
    {
        $iat = time();
        $exp = $iat + 60 * 60;

        $token = JWT::encode(
            [
                "iss" => "http://localhost",
                "aud" => "http://localhost",
                "iat" => $iat,
                "exp" => $exp,
                'user' => $user->getData()
            ],
            $this->authKey,
            self::ALGORITHM
        );

        return [
            'token' => $token,
            'exp'   => $exp,
        ];
    }


    /**
     * This function will authenticate the user based on the token.
     *
     * @param string $token The JWT token
     * @return void
     */
    public function auth(string $token): void
    {
        $this->response->setJsonType();

        try {
            $token = JWT::decode($token, new Key($this->authKey, 'HS256'));

            $user = new User();
            $user->loadData(get_object_vars($token->user));

            $this->user = $user;
        } catch (\Exception $e) {
            $this->response->setStatusCode($e->getCode());
            echo $e->getMessage();
        }
    }


    /**
     * This function logs the user out and just removes the user session
     * and sets the $this->user to null.
     *
     * @return boolean returns true if the logout was successful
     */
    public function logout(): bool
    {
        $this->user = null;
        return true;
    }


    /**
     * Returns true if the user is not logged in
     *
     * @return boolean
     */
    public static function isGuest(): bool
    {
        return is_null(self::$app->user);
    }
}
