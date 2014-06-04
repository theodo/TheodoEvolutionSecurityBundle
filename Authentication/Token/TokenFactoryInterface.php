<?php

namespace Theodo\Evolution\Bundle\SecurityBundle\Authentication\Token;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Creates an evolution token with different strategy.
 * 
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
interface TokenFactoryInterface
{
    /**
     * Create an EvolutionToken according to the event context.
     *
     * @param GetResponseEvent $event
     */
    public function create(GetResponseEvent $event);
}
 