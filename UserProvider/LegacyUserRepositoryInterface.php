<?php

namespace Theodo\Evolution\Bundle\SecurityBundle\UserProvider;

/**
 * Allows to find a Legacy User
 */
interface LegacyUserRepositoryInterface
{
    /**
     * @param  string       $username
     * @return \sfGuardUser guarduser
     */
    public function findOneByUsername($username);
}
