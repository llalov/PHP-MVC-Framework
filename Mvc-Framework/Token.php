<?php
declare(strict_types=1);

namespace Mvc;

/**
 * Class Token - CSRF Token
 * @package Mvc
 */
class Token
{
    private static $_instance;

    private function __construct()
    {

    }

    public static function init() : Token
    {
        if (self::$_instance == null) {
            self::$_instance = new Token();
        }

        return self::$_instance;
    }

    public static function render(bool $samePage = false)
    {
        if (!$samePage) {
            self::generateToken();
        }

        $html = '<input type="hidden" name="_token" value="' . App::getInstance()->getSession()->_token . '">';
        echo $html;
    }

    public static function validates($token) : bool
    {
        $isValid = App::getInstance()->getSession()->_token === $token;
        self::generateToken();
        return $isValid;
    }

    public static function getToken(bool $samePageToken = false)
    {
        if (!$samePageToken) {
            self::generateToken();
        }

        return App::getInstance()->getSession()->_token;
    }

    private static function generateToken()
    {
        App::getInstance()->getSession()->_token = base64_encode(openssl_random_pseudo_bytes(64));
    }
}