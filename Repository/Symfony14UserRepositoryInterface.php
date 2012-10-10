<?php
namespace Theodo\Evolution\SecurityBundle\Repository;

use Theodo\Evolution\SecurityBundle\UserProvider\LegacyUserRepositoryInterface;

/**
 * @author Marek Kalnik <marekk@theodo.fr>
 */
interface Symfony14UserRepositoryInterface extends LegacyUserRepositoryInterface
{
    /**
     * Finds and returns a user
     *
     * @param integer $userId
     *
     * @return null|mixed Null if no user found
     */
    public function findOneByUserId($userId);
}

