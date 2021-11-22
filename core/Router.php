<?php

declare(strict_types=1);

namespace app\core;

use app\core\exception\NotFoundException;


/**
 * This class handles all the routing.
 * 
 * all from registering routes to actually resolving them.
 */
class Router
{
    /**
     * The Request object used to be parsed to the route function.
     *
     * @var Request
     */
    private Request $request;

    /**
     * The Response object used to be parsed to the route function.
     *
     * @var Response
     */
    private Response $response;

    /**
     * A list of all registered routes. These are often added in index.php via
     * the following code:
     * 
     * $app->router->get('/', [SiteController::class, 'home']);
     * 
     * This will register the '/' route to the 'home()' function in The SiteController in the "controllers" folder
     *
     * @var array
     */
    protected array $routes = [];


    /**
     * The router constructor
     *
     * @param Request $request The Request object is requered
     * @param Response $response The Response object is requered
     */
    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }


    /**
     * This function registers all the get routes. This can be done via:
     * $router->get('/', [SiteController::class, 'home']);
     *
     * @param string $path The path of the route
     * @param array|string $callback If it's a string it expects it to be a view otherwise a function in a controller.
     * @return void
     */
    public function get(string $path, array|string $callback): void
    {
        $this->routes['get'][$path] = $callback;
    }


    /**
     * This function registers all the post routes. This can be done via:
     * $router->post('/contact', [SiteController::class, 'contact']);
     *
     * @param string $path The path of the route
     * @param array|string $callback If it's a string it expects it to be a view otherwise a function in a controller.
     * @return void
     */
    public function post(string $path, array|string $callback): void
    {
        $this->routes['post'][$path] = $callback;
    }


    /**
     * This function resolves the request and either renders a view
     * or calls a function thats in the $routes array.
     * 
     * This also executes middlewares
     *
     * @return string
     */
    public function resolve(): string
    {
        $path     = $this->request->getPath();
        $method   = $this->request->method();
        $callback = $this->routes[$method][$path] ?? false;

        if ($callback === false) {
            throw new NotFoundException();
        }

        if (is_string($callback)) {
            return Application::$app->view->renderView($callback);
        }

        if (is_array($callback)) {
            /** @var \app\core\Controller $controller */
            $controller = new $callback[0]();
            Application::$app->controller = $controller;
            $controller->action = $callback[1];
            $callback[0] = $controller;

            foreach ($controller->getMiddlewares() as $middleware) {
                $middleware->execute();
            }
        }

        return call_user_func($callback, $this->request, $this->response);
    }
}
