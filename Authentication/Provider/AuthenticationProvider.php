<?php

namespace Theodo\Evolution\SecurityBundle\Authentication\Provider;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Theodo\Evolution\SecurityBundle\Authentication\Token\EvolutionUserToken;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;

/**
 * Class AuthenticationProvider description
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class AuthenticationProvider implements AuthenticationProviderInterface
{
    /**
     * @var \Symfony\Component\Security\Core\User\UserProviderInterface
     */
    protected $userProvider;

    /**
     * @param UserProviderInterface $userProvider
     */
    public function __construct(UserProviderInterface $userProvider)
    {
        $this->userProvider = $userProvider;
    }

    /**
     * Attempts to authenticates a TokenInterface object.
     *
     * @param TokenInterface $token The TokenInterface instance to authenticate
     *
     * @return TokenInterface An authenticated TokenInterface instance, never null
     *
     * @throws AuthenticationException   if the authentication fails
     * @throws \InvalidArgumentException if the token is not supported
     */
    public function authenticate(TokenInterface $token)
    {
        if (false == $this->supports($token)) {
            throw new AuthenticationException('The given token is not supported by the authentication provider');
        }

        $user = $this->userProvider->loadUserByUsername($token->getUsername());

        if ($user) {
            if (false == $this->isLegacyAuthenticated($token)) {
                return $token;
            }

            // Recreate the token
            $authenticatedToken = new EvolutionUserToken($user->getRoles());
            $authenticatedToken->setUser($user);
            $authenticatedToken->setAuthenticated(true);

            return $authenticatedToken;
        }

        throw new UsernameNotFoundException(sprintf('No user with username "%s" found.', $token->getUsername()));
    }

    /**
     * Checks whether this provider supports the given token.
     *
     * @param TokenInterface $token A TokenInterface instance
     *
     * @return Boolean true if the implementation supports the Token, false otherwise
     */
    public function supports(TokenInterface $token)
    {
        return $token instanceof EvolutionUserToken;
    }

    /**
     * Check if the current user is authenticated in the legacy project.
     *
     * @return bool
     */
    public function isLegacyAuthenticated(TokenInterface $token)
    {
        if (false == $token->hasAttribute('is_authenticated')) {
            return false;
        }

        return $token->getAttribute('is_authenticated');
    }
}
