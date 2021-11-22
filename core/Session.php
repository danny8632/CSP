<?php

declare(strict_types=1);

namespace app\core;


/**
 * Handles all session related logic
 */
class Session
{
    protected const FLASH_KEY = 'flash_messages';


    public function __construct()
    {
        session_start();

        foreach (array_keys($_SESSION[self::FLASH_KEY] ?? []) as $key)
        {
            $_SESSION[self::FLASH_KEY][$key]['remove'] = true;
        }
    }


    public function setFlash($key, $message)
    {
        $_SESSION[self::FLASH_KEY][$key] = [
            'value' => $message,
            'remove' => false
        ];
    }

    public function getFlash($key)
    {
        return $_SESSION[self::FLASH_KEY][$key]['value'] ?? false;
    }


    public function __destruct()
    {
        $flashMessages = $_SESSION[self::FLASH_KEY] ?? [];

        foreach ($flashMessages as $key => $flashMessage)
        {
            if($flashMessage['remove'] === true)
            {
                unset($_SESSION[self::FLASH_KEY][$key]);
            }
        }
    }


    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    public function get($key)
    {
        return $_SESSION[$key] ?? false;
    }

    public function remove($key)
    {
        unset($_SESSION[$key]);
    }
}