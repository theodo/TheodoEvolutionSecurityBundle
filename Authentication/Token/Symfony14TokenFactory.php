<?php

namespace Theodo\Evolution\Bundle\SecurityBundle\Authentication\Token;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Theodo\Evolution\Bundle\SessionBundle\Manager\BagManagerConfigurationInterface;
use Theodo\Evolution\Bundle\SessionBundle\Manager\Symfony1\BagConfiguration;

/**
 * Symfony14TokenFactory
 * 
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class Symfony14TokenFactory implements TokenFactoryInterface
{
    /**
     * @var \Theodo\Evolution\Bundle\SessionBundle\Manager\BagManagerConfigurationInterface
     */
    private $bagConfiguration;

    public function __construct(BagManagerConfigurationInterface $bagConfiguration)
    {
        $this->bagConfiguration = $bagConfiguration;
    }

    /**
     * Create an EvolutionToken according to the event context.
     *
     * @param GetResponseEvent $event
     */
    public function create(GetResponseEvent $event)
    {
        $token = new EvolutionToken();

        $session = $event->getRequest()->getSession();

        $authBag = $session->getBag($this->bagConfiguration->getNamespace(BagConfiguration::AUTH_NAMESPACE));
        $attributesBag = $session->getBag($this->bagConfiguration->getNamespace(BagConfiguration::ATTRIBUTE_NAMESPACE));

        $token->setAuthenticated($authBag->get());
        $token->setUser($attributesBag->get('sfGuardSecurityUser.username'));

        return $token;
    }

}
 