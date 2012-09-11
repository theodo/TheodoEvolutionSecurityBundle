<?php

namespace TheodoEvolution\SecurityBundle\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

/**
 * EvolutionUserToken class
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class EvolutionUserToken extends AbstractToken
{
    /**
     * Returns the user credentials.
     *
     * @return mixed The user credentials
     */
    public function getCredentials()
    {
        return '';
    }
}
