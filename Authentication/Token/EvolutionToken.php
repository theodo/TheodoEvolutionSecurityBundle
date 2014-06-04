<?php

namespace Theodo\Evolution\Bundle\SecurityBundle\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

/**
 * Stores the evolution authentication informations.
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class EvolutionToken extends AbstractToken
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
