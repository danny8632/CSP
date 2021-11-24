<?php

namespace app\core\exception;


class NotFoundException extends \Exception
{
    protected $message = "Route or data not found";
    protected $code = 404;
}