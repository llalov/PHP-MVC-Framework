<?php
declare(strict_types=1);

namespace Mvc\Identity;

use Models\BindingModels\ChangePasswordBindingModel;
use Models\BindingModels\LoginBindingModel;
use Models\BindingModels\RegisterBindingModel;
use Models\ViewModels\UserController\ProfileViewModel;

interface IUserManager
{
    /**
     * @return mixed
     */
    static function getInstance();

    /**
     * @param RegisterBindingModel $model
     * @return int
     */
    function register(RegisterBindingModel $model) : int;

    /**
     * @param LoginBindingModel $model
     * @return string
     */
    function login(LoginBindingModel $model) : string;

    /**
     * @param ChangePasswordBindingModel $model
     * @return bool
     */
    function changePassword(ChangePasswordBindingModel $model) : bool;

    /**
     * @param string $username
     * @return bool
     */
    function usernameExists(string $username) : bool;

    /**
     * @param string $email
     * @return bool
     */
    function emailExists(string $email) : bool;

    /**
     * @param int $userId
     * @param int $roleId
     * @return bool
     */
    function addToRole(int $userId, int $roleId) : bool;

    /**
     * @param int $userId
     * @return ProfileViewModel
     */
    function getUserInfo(int $userId) : ProfileViewModel;
}