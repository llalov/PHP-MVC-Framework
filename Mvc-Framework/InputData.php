<?php
declare(strict_types=1);

namespace Mvc;

class InputData
{
    private static $_instance = null;
    private $_get = array();
    private $_post = array();
    private $_cookies = array();

    private function __construct()
    {
        $this->_cookies = $_COOKIE;
    }

    /**
     * @return InputData
     */
    public static function getInstance() : InputData
    {
        if (self::$_instance == null) {
            self::$_instance = new InputData();
        }

        return self::$_instance;
    }

    /**
     * @param array $get
     */
    public function setGet($get)
    {
        if (is_array($get)) {
            $this->_get = $get;
        }
    }

    /**
     * @param array $post
     */
    public function setPost($post)
    {
        if (is_array($post)) {
            $this->_post = $post;
        }
    }

    /**
     * Gets desired Get element by id, normalized or not, if none found return
     * given default value or null.
     * Usage example: get(0, 'trim|string|xss', 'new')
     * Gets element at 0, trims it, casts to string and check for xss. If there
     * is no 0 element will return 'new'.
     * @param $id
     * @param null $normalize string
     * @param null $default
     * @return string|null
     */
    public function get($id, $normalize = null, $default = null)
    {
        if ($this->hasGet($id)) {
            return Normalizer::normalize($this->_get[$id], $normalize);
        }

        return $default;
    }

    public function getForDb($name, $normalize = null, $default = null){
        $normalize = 'noescape|' . $normalize;

        return $this->get($name, $normalize, $default);
    }

    /**
     * Gets desired Post element by name, normalized or not, if none found return
     * given default value or null.
     * Usage example: post('test', 'trim|string|xss', 'new')
     * Gets element with name 'test', trims it, casts to string and check for xss. If there
     * is no element will return 'new'.
     * @param $name
     * @param null $normalize string
     * @param null $default
     * @return string|null
     */
    public function post($name, $normalize = null, $default = null)
    {
        if ($this->hasPost($name)) {
            return Normalizer::normalize($this->_post[$name], $normalize);
        }

        return $default;
    }

    public function postForDb($name, $normalize = null, $default = null){
        $normalize = 'noescape|' . $normalize;

        return $this->post($name, $normalize, $default);
    }

    /**
     * Gets desired Cookie element by name, normalized or not, if none found return
     * given default value or null.
     * Usage example: post('test', 'trim|string|xss', 'new')
     * Gets element with name 'test', trims it, casts to string and check for xss. If there
     * is no element will return 'new'.
     * @param $name
     * @param null $normalize string
     * @param null $default
     * @return string|null
     */
    public function cookies($name, $normalize = null, $default = null)
    {
        if ($this->hasCookies($name)) {
            return Normalizer::normalize($this->_cookies[$name], $normalize);
        }

        return $default;
    }

    public function cookiesForDb($name, $normalize = null, $default = null){
        $normalize = 'noescape|' . $normalize;

        return $this->cookies($name, $normalize, $default);
    }

    private function hasGet($id)
    {
        return array_key_exists($id, $this->_get);
    }

    private function hasPost($name)
    {
        return array_key_exists($name, $this->_post);
    }

    private function hasCookies($name)
    {
        return array_key_exists($name, $this->_cookies);
    }
}