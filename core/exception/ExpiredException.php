<?php

declare(strict_types=1);

namespace app\core\exception;


class ExpiredException extends \Exception
{
    protected $message = "The access token expired";
    protected $code = 401;
}
