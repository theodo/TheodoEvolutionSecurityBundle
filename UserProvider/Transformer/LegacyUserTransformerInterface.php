<?php

namespace Theodo\Evolution\Bundle\SecurityBundle\UserProvider\Transformer;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * This transforms a legacy user resource into a Symfony UserInterface
 * implementation.
 * 
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
interface LegacyUserTransformerInterface 
{
    /**
     * @param $user
     * @return UserInterface
     */
    public function transform($user);
}
 