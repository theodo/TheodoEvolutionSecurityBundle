<?php

namespace Theodo\Evolution\Bundle\SecurityBundle\UserProvider;

/**
 * Allows to find a Legacy User
 */
interface LegacyUserRepositoryInterface
{
    /**
     * @param  string $username
     * @return mixed
     */
    public function findOneByUsername($username);

    /**
     * @param  $id
     * @return mixed
     */
    public function findOneById($id);
}
