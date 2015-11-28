<?php
declare(strict_types=1);

namespace Mvc\Routers;

class DefaultRouter implements IRouter
{
    public function getURI() : string
    {
        return urldecode(strtolower(ltrim($_SERVER['REQUEST_URI'], '/')));
    }

    public function getPost()
    {
        return $_POST;
    }

    public function getRequestMethod()
    {
        return $_SERVER['REQUEST_METHOD'];
    }
}