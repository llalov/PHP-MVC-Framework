<?php
declare(strict_types=1);

namespace Mvc;

class Scanner
{
    /**
     * @var Scanner
     */
    private static $inst = null;
    /**
     * @var array
     */
    private $customRoutes = array();
    /**
     * @var array
     */
    private $actions = array();
    /**
     * Scanner constructor.
     */
    private function __construct(){
        $this->dispatch();
    }
    /**
     * @return Scanner
     */
    public static function getInstance() : Scanner
    {
        if (self::$inst == null){
            self::$inst = new Scanner();
        }
        return self::$inst;
    }
    private function dispatch(){
        if (Helpers::needScan("Config/actions.txt", "Controllers/.")) {
            $controllersNames = $this->getControllersNames();
            $this->scanControllersRoutes($controllersNames);
            Helpers::writeInFile("Config/actions.txt", strval(time()));
        } else {
            $this->loadCustomRoutes();
            $this->loadActions();
        }
    }
    private function scanControllersRoutes(array $controllersNames){
        foreach ($controllersNames as $controllersName) {
            $fullPath = "Framework\\" . "Controllers" . "\\" . $controllersName;
            $rc = new \ReflectionClass($fullPath);
            $methods = $rc->getMethods();
            foreach ($methods as $method) {
                $methodDoc = $method->getDocComment();
                if ($methodDoc && preg_match('/@NoAction/', $methodDoc, $dummy)) {
                    continue;
                }
                $requestMethods = array("GET");
                $action = $controllersName . "/" . $method->getName();
                if ($methodDoc && preg_match_all('/@(POST|PUT|DELETE|GET)/', $methodDoc, $requestMethodsAnnotations)) {
                    $requestMethods = $requestMethodsAnnotations[1];
                }
                $this->actions[$action] = array(
                    "methods" => $requestMethods,
                    "annotations" => [],
                    "params" => [],
                    "arguments" => []
                );
                if ($methodDoc && preg_match('/@Route\(([^\)]+)\)/', $methodDoc, $routeAnnotation)) {
                    $params = explode("/", $routeAnnotation[1]);
                    array_shift($params);
                    array_shift($params);
                    $this->customRoutes[$routeAnnotation[1]] = array(
                        "controller" => $controllersName,
                        "action" => $method->getName(),
                        "parameters" => $params,
                        "methods" => $requestMethods
                    );
                }
                if ($methodDoc && preg_match_all('/@@(\w+)(?:\(([^)\s\n*]+)\))*/', $methodDoc , $fieldMatch)) {
                    for ($i = 0; $i < count($fieldMatch[0]); $i++) {
                        $annotationName = AppConfig::ANNOTATIONS_NAMESPACE
                            . ucfirst($fieldMatch[1][$i])
                            . AppConfig::ANNOTATIONS_SUFFIX;
                        $this->actions[$action]["annotations"][$annotationName] = $fieldMatch[2][$i];
                    }
                }
                if ($methodDoc && preg_match_all('/@param\s+([^\s]+)\s+\$([^\s]+)/', $method->getDocComment(), $parameterType)){
                    for ($i = 0; $i < count($parameterType[0]); $i++) {
                        $this->actions[$action]["params"][$parameterType[2][$i]] = $parameterType[1][$i];
                    }
                }
            }
        }
        Helpers::writeInFile("Config/routes.json",json_encode($this->customRoutes));
        Helpers::writeInFile("Config/actions.json",json_encode($this->actions));
    }
    /**
     * @return array
     */
    private function getControllersNames() : array {
        $controllersNames = array();
        $path = "Controllers";
        $files = array_diff(scandir($path), array('..', '.'));
        foreach($files as $file)
        {
            $fullFileName = substr($file, 0, strlen($file) - 4);
            $controllersNames[] = $fullFileName;
        }
        return $controllersNames;
    }
    /**
     * @return array
     */
    public function getCustomRoutes() : array{
        return $this->customRoutes;
    }
    /**
     * @return array
     */
    public function getActions() : array{
        return $this->actions;
    }
    /**
     * @param $actionName
     * @return array
     * @throws Exception
     */
    public function getAction(string $actionName) : array {
        if (!array_key_exists($actionName, $this->actions)){
            throw new Exception("There is no such path!\nPath: " . $actionName);
        }
        return $this->actions[$actionName];
    }
    private function loadCustomRoutes(){
        if (file_exists("Config/routes.json")) {
            $fh = fopen("Config/routes.json", 'r');
            $routes = fgets($fh);
            fclose($fh);
            $this->customRoutes = json_decode($routes, true);
        }
    }
    private function loadActions() {
        if (file_exists("Config/actions.json")) {
            $fh = fopen("Config/actions.json", 'r');
            $actions = fgets($fh);
            fclose($fh);
            $this->actions = json_decode($actions, true);
        }
    }
}