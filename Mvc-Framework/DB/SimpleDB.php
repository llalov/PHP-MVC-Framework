<?php
declare(strict_types=1);

namespace Mvc\DB;

use Mvc\App;
use Mvc\Normalizer;

class SimpleDB
{
    private static $inst = [];
    protected $_connection = 'default';
    private $_db = null;
    /**
     * @var \PDO
     */
    private static $database = null;
    /**
     * @var \PDOStatement
     */
    private $_statement = null;
    private $_params = array();
    private $_sql;

    public function __construct($connection = null)
    {
        if ($connection instanceof \PDO) {
            $this->_db = $connection;
            self::$database = $connection;
        } else if ($connection != null) {
            $this->_db = App::getInstance()->getDbConnection($connection);
            self::$database = App::getInstance()->getDbConnection($connection);
            $this->_connection = $connection;
        } else {
            $this->_db = App::getInstance()->getDbConnection($this->_connection);
            self::$database = App::getInstance()->getDbConnection($this->_connection);
        }
    }

    public function prepare(string $sql, array $params = array(), array $pdoOptions = array()) : SimpleDB
    {
        $this->_statement = $this->_db->prepare($sql, $pdoOptions);
        $this->_params = $params;
        $this->_sql = $sql;
        return $this;
    }

    public function execute(array $params = array()) : SimpleDB
    {
        if ($params) {
            $this->_params = $params;
        }
        $this->_statement->execute($this->_params);
        return $this;
    }

    public function fetchAllAssoc(bool $escape = true)
    {
        $data = $this->_statement->fetchAll(\PDO::FETCH_ASSOC);
        if ($data === false) {
            return false;
        }

        if ($escape) {
            $escaped = array();
            foreach ($data as $elementKey => $elementData) {
                foreach ($elementData as $key => $value) {
                    $escaped[$elementKey][$key] = htmlentities($value);
                }

            }

            return $escaped;
        }

        return $data;
    }

    public function fetchRowAssoc(bool $escape = true)
    {
        $data = $this->_statement->fetch(\PDO::FETCH_ASSOC);
        if ($data === false) {
            return false;
        }

        if ($escape) {
            $escaped = array();
            foreach ($data as $key => $value) {
                $escaped[$key] = htmlentities($value);
            }

            return $escaped;
        }

        return $data;
    }

    /**
     * @param $instanceName
     * @throws \Exception
     * @return SimpleDB
     */
    public static function getInstance(string $instanceName = 'default') : SimpleDB
    {
        if (self::$inst[$instanceName] == null){
            throw new \Exception('Instance with that name was not set');
        }
        return self::$inst[$instanceName];
    }

    public function getLastInsertedId() : int
    {
        return $this->_db->lastInsertId();
    }

    /**
     * Can be used for custom use of PDO.
     */
    public function getStatement()
    {
        return $this->_statement;
    }

    public static function isAdmin() : bool
    {
        $statement = self::$database->prepare("SELECT u.id
                                                FROM user_roles ur
                                                JOIN users u
                                                ON u.id = ur.user_id
                                                WHERE (u.username = ? AND u.id = ?) AND ur.role_id = 2");
        $statement->bindParam(1, App::getInstance()->getSession()->_username);
        $statement->bindParam(2, App::getInstance()->getSession()->_login);
        $statement->execute();
        $response = $statement->fetch(\PDO::FETCH_ASSOC);
        if ($response) {
            $id = Normalizer::normalize($response['isAdmin'], 'bool');
            return true;
        }

        return false;
    }

    public function affectedRows() : int
    {
        return $this->_statement->rowCount();
    }
}