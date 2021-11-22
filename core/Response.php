<?php

declare(strict_types=1);

namespace app\core;


/**
 * This class handles all response logic.
 * Like setting the header, statusCode or redirecting
 */
class Response
{
    /**
     * Sets the response status code
     *
     * @param integer $code The code fx: 404
     * @return void
     */
    public function setStatusCode(int $code): void
    {
        http_response_code($code);
    }


    /**
     * Redirects the user.
     *
     * @param string $url The url to redirect to
     * @return void
     */
    public function redirect(string $url): void
    {
        header("Location: $url");
    }
}
