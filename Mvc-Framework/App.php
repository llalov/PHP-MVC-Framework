<?php

namespace Mvc;

use Mvc\Routers\DefaultRouter;
use Mvc\Routers\IRouter;
use Mvc\Sessions\ISession;
use Mvc\Sessions\NativeSession;

include_once 'Loader.php';

class App
{
    private static $_instance = null;
    /**
     * @var Config
     */
    private $_config = null;
    private $_frontController = null;
    private $_router = null;
    private $_dbConnections = array();
    /**
     * @var ISession
     */
    private $_session = null;

    private function __construct()
    {
        set_exception_handler(array($this, '_exceptionHandler'));
        Loader::registerNamespace('Mvc', dirname(__FILE__) . DIRECTORY_SEPARATOR);
        Loader::registerAutoLoad();
        $this->_config = Config::getInstance();
    }

    public function getConfigFolder() : string
    {
        return $this->_config->getConfigFolder();
    }

    public function setConfigFolder(string $path)
    {
        $this->_config->setConfigFolder($path);
    }

    public function getRouter()
    {
        return $this->_router;
    }

    function setRouter($router)
    {
        $this->_router = $router;
    }

    /**
     * @return App
     */
    public static function getInstance() : App
    {
        if (self::$_instance == null) {
            self::$_instance = new App();
        }

        return self::$_instance;
    }

    /**
     * @return Config
     */
    public function getConfig() : Config
    {
        return $this->_config;
    }

    public function getDbConnection(string $connection = 'default')
    {
        if (!$connection) {
            throw new \Exception('No connection string provided', 500);
        }

        if ($this->_dbConnections[$connection]) {
            return $this->_dbConnections[$connection];
        }

        $dbConfig = $this->getConfig()->database;
        if (!$dbConfig[$connection]) {
            throw new \Exception('No valid connection string found in config file', 500);
        }

        $database = new \PDO(
            $dbConfig[$connection]['connection_url'],
            $dbConfig[$connection]['username'],
            $dbConfig[$connection]['password'],
            $dbConfig[$connection]['pdo_options']
        );
        $this->_dbConnections[$connection] = $database;
        return $database;
    }

    /**
     * @return ISession
     */
    public function getSession()
    {
        return $this->_session;
    }

    public function setSession(ISession $session)
    {
        $this->_session = $session;
    }

    public function run()
    {
        if ($this->_config->getConfigFolder() == null) {
            $this->setConfigFolder('../config');
        }

        $this->_frontController = FrontController::getInstance();
        if ($this->_router instanceof IRouter) {
            $this->_frontController->setRouter($this->_router);
        }

        if ($this->_router == null) {
            // Can add here more routers
            $this->_frontController->setRouter(new DefaultRouter());
        }

        if ($this->_session == null) {
            $sessionInfo = $this->_config->app['session'];
            if ($sessionInfo['auto_start']) {
                if ($sessionInfo['type'] == 'native') {
                    $this->_session = new NativeSession(
                        $sessionInfo['name'],
                        $sessionInfo['lifetime'],
                        $sessionInfo['path'],
                        $sessionInfo['domain'],
                        $sessionInfo['secure']
                    );
                }
            }
        }

        $this->_frontController->dispatch();
    }

    public function _exceptionHandler( $ex)
    {
        if ($this->_config && $this->_config->app['displayExceptions'] == true) {
            echo '<pre>' . print_r($ex, true) . '</pre>';
        } else {
            $this->displayError($ex->getCode(), $ex->getMessage());
        }
    }

    public function displayError(string $error, string $message)
    {
        echo '<link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">';
        echo '<div class="text-center">';
        FormViewHelper::init()->initLink()->setValue('Home')->setAttribute('href', '/')->create()->render();
        $confError = $this->_config->errors[$error];
        if ($confError) {
            if ($confError == 'message') {
                echo '<h1>' . $message . '</h1>';
            } else {
                echo "<h1> $confError </h1>";
            }
        } else {
            echo '<h1>Oooops, something went wrong ;(. Error  ' . $error . '</h1>';
            echo '<img class="decoded shrinkToFit" alt="http://media.topito.com/wp-content/uploads/2011/09/lama_bizarre012.jpg" src="http://media.topito.com/wp-content/uploads/2011/09/lama_bizarre012.jpg" height="340" width="453">';
        }

        echo '</div>';
        exit;
    }

    public function __destruct()
    {
        if ($this->_session != null) {
            $this->_session->saveSession();
        }
    }

    public function getUsername() : string
    {
        return $this->_session->escapedUsername;
    }

    public function isLogged(): bool
    {
        return $this->_session->escapedUsername !== null;
    }
}