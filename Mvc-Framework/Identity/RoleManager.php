<?php
declare(strict_types=1);

namespace Mvc\Identity;

use Mvc\DB\SimpleDB;

class RoleManager implements IRoleManager
{
    /**
     * @var IRoleManager
     */
    protected static $inst = null;

    /**
     * RoleManager constructor.
     */
    private function __construct()
    {
    }

    public static function getInstance() : IRoleManager
    {
        if(self::$inst == null) {
            self::$inst = new RoleManager();
        }

        return self::$inst;
    }

    /**
     * @param $roleName
     * @return bool
     */
    function createRole(string $roleName) : bool
    {
        $db = SimpleDB::getInstance('conference_scheduler');

        if($this->exists($roleName)){
            throw new \Exception("There is already a role $roleName in the database.");
        }

        $result = $db->prepare("INSERT INTO roles (name) VALUES (?)");
        $result->execute([$roleName]);

        return $result->affectedRows() > 0;
    }

    /**
     * @param $roleName
     * @return bool
     */
    function exists(string $roleName) : bool
    {
        $db = SimpleDB::getInstance('conference_scheduler');

        $result = $db->prepare("SELECT id FROM roles WHERE name = ?");
        $result->execute([$roleName]);

        return $result->affectedRows() > 0;
    }

    /**
     * @param $roleName
     * @return int
     */
    function getRoleId(string $roleName) : int
    {
        $db = SimpleDB::getInstance("conference_scheduler");

        $result = $db->prepare("SELECT id FROM roles WHERE name = ?");
        $result->execute([$roleName]);

        if($result->affectedRows() === 0) {
            throw new \Exception("Role $roleName doesn't exist in the database.");
        }

        return  intval($result->fetch()["id"]);
    }
}