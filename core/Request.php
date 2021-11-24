<?php

declare(strict_types=1);

namespace app\core;


/**
 * This class manages getting the requestdata, the path and method.
 * This is where all data needed from the request should be fetched from.
 */
class Request
{
    /**
     * Returns the request path fx: /api/user
     *
     * @return string The path : /api/user
     */
    public function getPath(): string
    {
        $path = str_replace('/api', '', $_SERVER['REQUEST_URI'] ?? '/');
        $position = strpos($path, '?');

        if ($position === false) {
            return $path;
        }

        return substr($path, 0, $position);
    }


    /**
     * Returns the request method in lowercase fx: get
     *
     * @return string The method
     */
    public function method(): string
    {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }


    /**
     * Helper function to check if the method is a get
     *
     * @return bool true if the method is a get method
     */
    public function isGet(): bool
    {
        return $this->method() === 'get';
    }


    /**
     * Helper function to check if the method is a post
     *
     * @return bool true if the method is a post method
     */
    public function isPost(): bool
    {
        return $this->method() === 'post';
    }


    /**
     * Helper function to check if the method is a delete
     *
     * @return bool true if the method is a delete method
     */
    public function isDelete(): bool
    {
        return $this->method() === 'delete';
    }


    /**
     * Will return all the request body data sanitized so it's safe to use
     *
     * @return array The sanitized body data
     */
    public function getBody(): array
    {
        $body        = [];
        $requestKeys = [];
        $filterType  = -10;

        if ($this->isGet()) {
            $requestKeys = array_keys($_GET);
            $filterType  = INPUT_GET;
        }

        if ($this->isPost() || $this->isDelete()) {
            $requestKeys = array_keys($_POST);
            $filterType  = INPUT_POST;
        }

        if ($filterType !== -10) {
            foreach ($requestKeys as $key) {
                $body[$key] = filter_input($filterType, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            }
        }

        $json = file_get_contents('php://input');
        if ($json !== false) {
            $body = array_merge($body, json_decode($json, true) ?? []);
        }

        return $body;
    }


    /**
     * Returns all request headers
     *
     * @return array list of all headers
     */
    public function getHeaders(): array
    {
        try {
            return getallheaders();
        } catch (\Throwable $th) {
            return [];
        }
    }


    /**
     * Returns the JWT token from the header
     *
     * @return string|boolean false if theres no token
     */
    public function getAuthToken(): string|bool
    {
        $headers = $this->getHeaders();
        $token   = str_replace('Bearer ', '', $headers['Authorization'] ?? '');

        if (strlen($token) === 0) {
            return false;
        }

        return $token;
    }
}
