<?php

declare(strict_types=1);

namespace app\core;

use app\core\db\Database;


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
    public ?UserModel $user;

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
        self::$ROOT_DIR  = $rootPath;
        self::$app       = $this;

        $this->request  = new Request();
        $this->response = new Response();
        $this->session  = new Session();
        $this->router   = new Router($this->request, $this->response);
        $this->db       = new Database($config['db']);


        //  Sets the user object if the session has the users primaryValue(the Id of the record in db)
        //  else the user will be set to null and this is a guest login
        $primaryValue = $this->session->get('user');
        if ($primaryValue) {
            $primaryKey = (new $this->userClass)->primaryKey();
            $this->user = $this->userClass::findOne([$primaryKey => $primaryValue]);
        } else {
            $this->user = null;
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
        try {
            echo $this->router->resolve();
        } catch (\Exception $e) {
            $this->response->setStatusCode($e->getCode());
            echo $this->view->renderView('_error', [
                'exception' => $e
            ]);
        }
    }


    /**
     * This function logs the user in and set the users primarykey
     * into the session so it can be fetched on later requests.
     *
     * @param UserModel $user
     * @return boolean returns true if the session was successfully saved
     */
    public function login(UserModel $user): bool
    {
        $this->user = $user;
        $this->session->set('user', $user->{$user->primaryKey()});

        return true;
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
        $this->session->remove('user');

        return true;
    }


    /**
     * Returns true if the user is not logged in
     *
     * @return boolean
     */
    public static function isGuest(): bool
    {
        return !self::$app->user;
    }
}
