<?php

namespace Theodo\Evolution\SecurityBundle\UserProvider;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Theodo\Evolution\SecurityBundle\User\SfGuardEvolutionUser;
use Theodo\Evolution\SecurityBundle\UserProvider\LegacyUserRepositoryInterface;

/**
 * Class UserProvider description
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class SfGuardUserProvider implements UserProviderInterface
{
    protected $userRepository;

    public function __construct(LegacyUserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Loads the user for the given username.
     *
     * This method must throw UsernameNotFoundException if the user is not
     * found.
     *
     * @param string $username The username
     *
     * @return UserInterface
     *
     * @see UsernameNotFoundException
     *
     * @throws UsernameNotFoundException if the user is not found
     *
     */
    public function loadUserByUsername($username)
    {
        $guardUser = $this->userRepository->findOneByUsername($username);

        if (!$guardUser) {
            throw new UsernameNotFoundException(sprintf('The username "%s" has not been found', $username));
        }

        return new SfGuardEvolutionUser($guardUser);
    }

    /**
     * Refreshes the user for the account interface.
     *
     * It is up to the implementation to decide if the user data should be
     * totally reloaded (e.g. from the database), or if the UserInterface
     * object can just be merged into some internal array of users / identity
     * map.
     * @param UserInterface $user
     *
     * @return UserInterface
     *
     * @throws UnsupportedUserException if the account is not supported
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof SfGuardEvolutionUser) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * Whether this provider supports the given user class
     *
     * @param string $class
     *
     * @return Boolean
     */
    public function supportsClass($class)
    {
        return $class === 'Theodo\Evolution\SecurityBundle\EvolutionUser';
    }
}
