<?php

namespace Mvc\Sessions;

class NativeSession implements ISession
{
    public function __construct($name, $lifetime = 3600, $path = null, $domain = null, $secure = false)
    {
        if (strlen($name) < 1) {
            $name = 'session';
        }

        session_name($name);

        session_set_cookie_params($lifetime, $path, $domain, $secure, true);
        session_start();
    }

    public function getSessionId()
    {
        return session_id();
    }

    public function saveSession()
    {
        session_write_close();
    }

    public function destroySession()
    {
        session_destroy();
    }

    public function __get($name)
    {
        return $_SESSION[$name];
    }

    public function __set($name, $value)
    {
        $_SESSION[$name] = $value;
    }
}