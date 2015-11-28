<?php
declare(strict_types=1);

namespace Mvc\HttpContext;

use Mvc\Normalizer;

class HttpContext
{
    private static $instance = null;
    private $get = array();
    private $post = array();
    private $cookies = array();
    private $session = array();
    private $method = 'get';

    public function __construct()
    {
    }

    public function setPost($ar) {
        if (is_array($ar)) {
            $this->post = $ar;
        }
    }
    public function setGet($ar) {
        if (is_array($ar)) {
            $this->get = $ar;
        }
    }
    public function setCookies($ar) {
        if (is_array($ar)) {
            $this->cookies = $ar;
        }
    }
    public function setSession($ar) {
        if (is_array($ar)) {
            $this->session = $ar;
        }
    }
    public function setMethod($method)
    {
        $this->method = $method;
    }
    public function hasGet($id) {
        return array_key_exists($id, $this->get);
    }
    public function hasPost($name) {
        return array_key_exists($name, $this->post);
    }
    public function hasSession($name) {
        return array_key_exists($name, $this->session);
    }
    public function hasCookies($name) {
        return array_key_exists($name, $this->cookies);
    }
    public function get($id, $normalize = null, $default = null) {
        if ($this->hasGet($id)) {
            if ($normalize != null) {
                return Normalizer::normalize($this->get[$id], $normalize);
            }
            return $this->get[$id];
        }
        return $default;
    }
    public function post($name, $normalize = null, $default = null) {
        if ($this->hasPost($name)) {
            if ($normalize != null) {
                return Normalizer::normalize($this->post[$name], $normalize);
            }
            return $this->post[$name];
        }
        return $default;
    }
    public function cookies($name, $normalize = null, $default = null) {
        if ($this->hasCookies($name)) {
            if ($normalize != null) {
                return Normalizer::normalize($this->cookies[$name], $normalize);
            }
            return $this->cookies[$name];
        }
        return $default;
    }
    public function session($name, $normalize = null, $default = null) {
        if ($this->hasSession($name)) {
            if ($normalize != null) {
                return Normalizer::normalize($this->session[$name], $normalize);
            }
            return $this->session[$name];
        }
        return $default;
    }
    public function getMethod()
    {
        return $this->method;
    }
    public function isGet()
    {
        return $this->method == 'get';
    }
    public function isPost()
    {
        return $this->method == 'post';
    }
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new HttpContext();
        }
        return self::$instance;
    }
}