<?php
declare(strict_types=1);

namespace Mvc\Identity;

use Models\BindingModels\ChangePasswordBindingModel;
use Mvc\DB\SimpleDB;
use Models\BindingModels\LoginBindingModel;
use Models\BindingModels\RegisterBindingModel;
use Models\ViewModels\UserController\ProfileViewModel;

class UserManager implements IUserManager
{
    /**
     * @var IUserManager
     */
    protected static $inst = null;

    /**
     * UserManager constructor.
     */
    private function __construct()
    {
    }

    /**
     * @return IUserManager
     */
    public static function getInstance() : IUserManager
    {
        if(self::$inst == null) {
            self::$inst = new UserManager();
        }

        return self::$inst;
    }

    /**
     * @param RegisterBindingModel $model
     * @return int
     * @throws \Exception
     */
    function register(RegisterBindingModel $model) : int
    {
        $db = SimpleDB::getInstance('conference_scheduler');
        $username = $model->getUsername();

        if(self::usernameExists($model->getUsername())) {
            throw new \Exception("Username $username already exists.");
        }

        $result = $db->prepare("INSERT INTO users (username, password, email)
                                VALUES(?, ?, ?)");
        $result->execute([
            $model->getUsername(),
            $model->getPassword(),
            $model->getEmail()
        ]);

        if($result->affectedRows() < 1) {
            throw new \Exception("Cannot register the user.");
        }

        return intval($db->getLastInsertedId());
    }

    /**
     * @param LoginBindingModel $model
     * @return string
     */
    function login(LoginBindingModel $model) : string
    {
        $db = SimpleDB::getInstance('conference_scheduler');

        $result = $db->prepare("SELECT
                                id, username, password
                                FROM users
                                WHERE username = ?");

        $result->execute([$model->getPassword()]);

        if($result->affectedRows() > 0) {
            $userRow = $result->fetch();

            if(password_verify($model->getPassword(), $userRow['password'])){
                return $userRow['id'];
            }
        }

        throw new \Exception("Wrong username or password.");
    }

    /**
     * @param ChangePasswordBindingModel $model
     * @return bool
     */
    function changePassword(ChangePasswordBindingModel $model) : bool
    {
        $db = SimpleDB::getInstance('conference_scheduler');
        $result = $db->prepare("SELECT password FROM users WHERE id = ?");
        $result->execute([
            $_SESSION['userId']
        ]);
        $password = $result->fetch()["password"];
        if (!password_verify($model->getCurrentPassword(), $password)) {
            throw new \Exception("Wrong current password!");
        }
        $result = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
        $result->execute([
            password_hash($model->getPassword(), PASSWORD_DEFAULT),
            $_SESSION['userId']
        ]);
        return $result->rowCount() > 0;
    }

    /**
     * @param string $username
     * @return bool
     */
    function usernameExists(string $username) : bool
    {
        $db = SimpleDB::getInstance('conference_scheduler');
        $result = $db->prepare("SELECT id FROM users WHERE username = ?");
        $result->execute([$username]);
        return $result->rowCount() > 0;
    }

    /**
     * @param string $email
     * @return bool
     */
    function emailExists(string $email) : bool
    {
        $db = SimpleDB::getInstance('conference_scheduler');
        $result = $db->prepare("SELECT id FROM users WHERE email = ?");
        $result->execute([$email]);
        return $result->rowCount() > 0;
    }

    /**
     * @param int $userId
     * @param int $roleId
     * @return bool
     */
    function addToRole(int $userId, int $roleId) : bool
    {
        $db = SimpleDB::getInstance('conference_scheduler');
        $result = $db->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
        $result->execute([$userId, $roleId]);
        return $result->rowCount() > 0;
    }

    /**
     * @param int $userId
     * @return ProfileViewModel
     */
    function getUserInfo(int $userId) : ProfileViewModel
    {
        $db = SimpleDB::getInstance('conference_scheduler');
        $result = $db->prepare("SELECT
                                username, email
                                FROM users
                                WHERE id = ?");
        $result->execute([$userId]);
        $userRow = $result->fetch();

        $user = new ProfileViewModel($userRow['username'], $userRow['email']);

        return $user;
    }
}