<?php
declare(strict_types=1);

namespace Mvc\Identity;

interface IRoleManager
{
    /**
     * @param $roleName
     * @return bool
     */
    function createRole(string $roleName) : bool;

    /**
     * @param $roleName
     * @return bool
     */
    function exists(string $roleName) : bool;

    /**
     * @param $roleName
     * @return int
     */
    function getRoleId(string $roleName) : int;
}