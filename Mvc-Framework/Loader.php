<?php
declare(strict_types=1);

namespace Mvc;

/**
 * Auto loads classes when a class is not included.
 * @package Mvc
 */
final class Loader
{
    private static $namespaces = array();

    private function __construct()
    {

    }

    public static function registerAutoLoad()
    {
        spl_autoload_register(array("\\Mvc\\Loader", 'autoload'));
    }

    public static function autoload(string $class)
    {
        self::loadClass($class);
    }

    public static function loadClass(string $class)
    {
        foreach (self::$namespaces as $namespace => $path) {
            if (strpos($class, $namespace) === 0) {
                $invariantSystemPath = str_replace('\\', DIRECTORY_SEPARATOR, $class);
                $filePath = substr_replace($invariantSystemPath, $path, 0, strlen($namespace)) . '.php';
                $realPath = realpath($filePath);
                if ($realPath && is_readable($realPath)) {
                    include $realPath;
                } else {
                    throw new \Exception('File cannot be included: ' . $filePath, 404);
                }

                break;
            }
        }

    }

    public static function registerNamespace(string $namespace, string $path)
    {
        $namespace = trim($namespace);
        if (strlen($namespace) > 0) {
            if (!$path) {
                throw new \Exception('Invalid path: ' . $namespace);
            }

            $realPath = realpath($path);
            if ($realPath && is_dir($realPath) && is_readable($realPath)) {
                self::$namespaces[$namespace . '\\'] = $realPath . DIRECTORY_SEPARATOR;
            } else {
                throw new \Exception('Namespace directory read error in:' . $path);
            }
        } else {
            throw new \Exception('Invalid namespace: ' . $namespace);
        }
    }

    public static function registerNamespaces($namespaces)
    {
        if (is_array($namespaces)) {
            foreach ($namespaces as $namespace => $path) {
                self::registerNamespace($namespace, $path);
            }
        } else {
            throw new \Exception('Invalid namespaces!');
        }
    }
}