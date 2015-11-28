<?php
declare(strict_types=1);

namespace Mvc;

class Config
{

    private static $_instance = null;
    private $_configFolder = null;
    private $_configArray = array();

    private function __construct()
    {
    }

    public function setConfigFolder(string $configFolder)
    {
        if (!$configFolder) {
            throw new \Exception('Empty config folder path.');
        }

        $realPath = realpath($configFolder);
        if ($realPath != false && is_dir($realPath) && is_readable($realPath)) {
            $this->_configArray = array();
            $this->_configFolder = $realPath . DIRECTORY_SEPARATOR;

            $namespaces = $this->app['namespaces'];

            if (is_array($namespaces)) {
                Loader::registerNamespaces($namespaces);
            }
        } else {
            throw new \Exception('Config directory read error: ' . $configFolder);
        }
    }

    public static function getInstance() : Config
    {
        if (self::$_instance == null) {
            self::$_instance = new Config();
        }

        return self::$_instance;
    }

    public function getConfigFolder()
    {
        return $this->_configFolder;
    }

    public function includeConfigFile(string $path)
    {
        if (!$path) {
            throw new \Exception('Empty config path');
        }

        $file = realpath($path);
        if ($file != false && is_file($file) && is_readable($file)) {
            $baseName = explode('.php', basename($file))[0];
            $this->_configArray[$baseName] = include $file;
        } else {
            throw new \Exception('Config file read error: ' . $path);
        }
    }

    public function __get(string $name)
    {
        if (!$this->_configArray[$name]) {
            $this->includeConfigFile($this->_configFolder . $name . '.php');
        }

        if (array_key_exists($name, $this->_configArray)) {
            return $this->_configArray[$name];
        }

        return null;
    }
}