<?php

namespace Theodo\Evolution\Bundle\SecurityBundle\UserProvider;

use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * LegacyUserProviderInterface
 *
 * @see UserProviderInterface
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
interface LegacyUserProviderInterface extends UserProviderInterface
{
    /**
     * Loads the user for the given user id.
     *
     * This method must throw UserIdNotFoundException if the user is not
     * found.
     *
     * @param int $id The user id
     *
     * @return UserInterface
     *
     * @see UserIdNotFoundException
     *
     * @throws UserIdNotFoundException if the user is not found
     *
     */
    public function loadUserById($id);
}
 