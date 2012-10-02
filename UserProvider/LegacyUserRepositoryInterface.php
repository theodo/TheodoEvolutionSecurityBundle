<?php

namespace Theodo\Evolution\SecurityBundle\UserProvider;

/**
 * Allows to find a Legacy User
 */
interface LegacyUserRepositoryInterface
{
    /**
     * @param string $username
     * @return \sfGuardUser guarduser
     */
    public function findOneByUsername($username);
}


