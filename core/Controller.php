<?php

declare(strict_types=1);

namespace app\core;

use app\core\middlewares\BaseMiddleware;


/**
 * The controller class is what all controllers extends from.
 * The job for this controller is to handle middleware and default values
 * for all the controllers
 */
class Controller
{
    /**
     * The layout that the view is going to be rendered in.
     * @default main
     *
     * @var string
     */
    public string $layout = 'main';

    /**
     * The actual function of the controller that is being called
     * when the router->resolve() function is being called.
     * This is also set in the Router class. 
     *
     * @var string
     */
    public string $action = '';

    /**
     * Contains all the middleware that is assigned to this controller.
     * This should be set in the constructor of the controller via the registerMiddleware() function.
     *
     * @var BaseMiddleware[]
     */
    protected array $middlewares = [];


    /**
     * This is a setter for the layout if the default layout is not wanted.
     *
     * @param string $layout The name of the layout/file in the views/layouts folder
     * @return void
     */
    public function setLayout(string $layout): void
    {
        $this->layout = $layout;
    }


    /**
     * Helper function to easy call renderView from the view class.
     *
     * @param string $view The name of the view/file from the views folder
     * @param array $params The params should be a key-value array where the key is
     * the desired variable name that is going to be accessible in the view file.
     * So if the params is [ 'meme' => 4 ]. Then the $meme variable will contain 4.
     * 
     * @return string
     */
    public function render(string $view, array $params = []): string
    {
        return Application::$app->view->renderView($view, $params);
    }


    /**
     * This is a setter for the middleware.
     *
     * @param BaseMiddleware $middleware The middleware thats being added to the controller
     * @return void
     */
    public function registerMiddleware(BaseMiddleware $middleware): void
    {
        $this->middlewares[] = $middleware;
    }


    /**
     * This is a getter for the middleware
     *
     * @return array
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }
}
