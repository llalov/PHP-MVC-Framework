<?php

namespace Mvc;

class Injector
{
    private static $_instance;
    private static $_config;

    private function __construct()
    {
        self::$_config = App::getInstance()->getConfig()->inject;
    }

    public static function getInstance() : Injector
    {
        if (self::$_instance === null) {
            self::$_instance = new Injector();
        }

        return self::$_instance;
    }

    /**
     * @param $config
     * @throws \Exception
     */
    public function setConfig(string $config)
    {
        if (is_string($config)) {
            self::$_config = App::getInstance()->getConfig()->$config;
        } else {
            throw new \Exception('Invalid config, string name expected!', 500);
        }
    }

    public function loadDependencies($class)
    {
        $reflectionClass = new \ReflectionClass($class);
        $properties = $reflectionClass->getProperties();
        $regex = '/@(?:i|I)nject\s*(.+)/';
        foreach ($properties as $property) {
            $doc = $property->getDocComment();
            preg_match($regex, $doc, $matches);
            if ($matches[1]) {
                $name = trim($matches[1]);
                $dependency = self::$_config[$name];
                if ($dependency !== null) {
                    $property = $reflectionClass->getProperty($property->getName());
                    $property->setAccessible(true);
                    $dep = new $dependency;
                    $this->loadDependencies($dep);
                    $property->setValue($class, $dep);
                } else {
                    throw new \Exception("No path found for '$name', check your config. Default is 'inject'.", 500);
                }
            }
        }
    }
}