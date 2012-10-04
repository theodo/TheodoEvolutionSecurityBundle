<?php

namespace Theodo\Evolution\SecurityBundle\User;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * This class is a Bridge to assure the legacy model compatibility with the UserInterface.
 * It may be also compatible with any objects implementing the same interface.
 * 
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 * @author Marek Kalnik <marekk@theodo.fr>
 */
class SfGuardEvolutionUser implements UserInterface
{
    /**
     * @var \sfGuardUser
     */
    private $guardUser;

    /**
     * @var Role[]
     */
    private $roles = array();

    public function __construct($user)
    {
        $this->guardUser = $user;
    }

    /**
     * Returns the roles granted to the user.
     *
     * <code>
     * public function getRoles()
     * {
     *     return array('ROLE_USER');
     * }
     * </code>
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return Role[] The user roles
     */
    public function getRoles()
    {
        if (true == $this->guardUser->getIsSuperAdmin()) {
            $this->roles[] = 'ROLE_LEGACY_ADMIN';
        }

        $legacyCredentials = $this->guardUser->getAllPermissionNames();
        foreach ($legacyCredentials as $credential) {
            $this->roles[] = 'ROLE_LEGACY_'.strtoupper($credential);
        }

        return $this->roles;
    }

    /**
     * Returns the password used to authenticate the user.
     *
     * This should be the encoded password. On authentication, a plain-text
     * password will be salted, encoded, and then compared to this value.
     *
     * @return string The password
     */
    public function getPassword()
    {
        return $this->guardUser->getPassword();
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string The salt
     */
    public function getSalt()
    {
        return $this->guardUser->getSalt();
    }

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUsername()
    {
        return $this->guardUser->getUsername();
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     *
     * @return void
     */
    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    /**
     * @return \sfGuardUser
     */
    public function getGuardUser()
    {
        return $this->guardUser;
    }
}
