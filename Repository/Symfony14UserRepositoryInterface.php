<?php
namespace Theodo\Evolution\SecurityBundle\Repository;

/**
 * @author Marek Kalnik <marekk@theodo.fr>
 */
interface Symfony14UserRepositoryInterface
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
