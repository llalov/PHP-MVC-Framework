<?php
declare(strict_types=1);

namespace Mvc\Identity;

class UserRoles
{
    /**
     * @var
     * @Field user_id
     * @Type INT
     * @Length 11
     * @NotNull
     */
    private $userId;
    /**
     * @var
     * @Field role_id
     * @Type INT
     * @Length 11
     * @NotNull
     */
    private $roleId;
}