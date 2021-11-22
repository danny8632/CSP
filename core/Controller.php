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
