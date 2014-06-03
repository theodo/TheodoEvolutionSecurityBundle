<?php

namespace Theodo\Evolution\Bundle\SecurityBundle\UserProvider;

use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Theodo\Evolution\Bundle\SecurityBundle\Exception\UserIdNotFoundException;
use Theodo\Evolution\Bundle\SecurityBundle\UserProvider\Transformer\LegacyUserTransformerInterface;

/**
 * LegacyUserProvider
 * 
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class LegacyUserProvider implements LegacyUserProviderInterface
{
    /**
     * @var LegacyUserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var Transformer\LegacyUserTransformerInterface
     */
    private $transformer;

    /**
     * @param LegacyUserRepositoryInterface  $userRepository
     * @param LegacyUserTransformerInterface $transformer
     */
    public function __construct(LegacyUserRepositoryInterface $userRepository, LegacyUserTransformerInterface $transformer)
    {
        $this->userRepository = $userRepository;
        $this->transformer    = $transformer;
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
        $user = $this->userRepository->findOneByUsername($username);

        if (null == $user) {
            $e = new UsernameNotFoundException();
            $e->setUsername($username);

            throw $e;
        }

        return $this->transformer->transform($user);
    }

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
    public function loadUserById($id)
    {
        $user = $this->userRepository->findOneById($id);

        if (null == $user) {
            $e = new UserIdNotFoundException();
            $e->setId($id);

            throw $e;
        }

        return $this->transformer->transform($user);
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
        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * Whether this provider supports the given user class
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsClass($class)
    {
        return true;
    }
}
 