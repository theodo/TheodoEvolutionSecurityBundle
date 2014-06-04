<?php

namespace Theodo\Evolution\Bundle\SecurityBundle\Firewall;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Theodo\Evolution\Bundle\SecurityBundle\Authentication\Token\EvolutionToken;
use Theodo\Evolution\Bundle\SecurityBundle\UserProvider\LegacyUserProviderInterface;
use Theodo\Evolution\Bundle\SessionBundle\Manager\BagManagerConfigurationInterface;

/**
 * Class EvolutionSecurityListener description
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class EvolutionSecurityListener implements ListenerInterface
{
    /**
     * @var \Symfony\Component\Security\Core\SecurityContextInterface
     */
    protected $securityContext;

    /**
     * @var \Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface
     */
    protected $authenticationManager;

    /**
     * @var LegacyUserProviderInterface
     */
    protected $provider;

    /**
     * @var null|\Symfony\Bridge\Monolog\Logger
     */
    protected $logger;

    /**
     * @param SecurityContextInterface         $securityContext
     * @param AuthenticationManagerInterface   $authenticationManager
     * @param LegacyUserProviderInterface      $provider
     * @param null|LoggerInterface             $logger
     */
    public function __construct(SecurityContextInterface $securityContext, AuthenticationManagerInterface $authenticationManager, LegacyUserProviderInterface $provider, LoggerInterface $logger = null)
    {
        $this->securityContext = $securityContext;
        $this->authenticationManager = $authenticationManager;
        $this->userProvider = $provider;
        $this->logger = $logger;
    }

    /**
     * This interface must be implemented by firewall listeners.
     *
     * @param GetResponseEvent $event
     */
    public function handle(GetResponseEvent $event)
    {
        if (null == $token = $this->securityContext->getToken()) {
            $token = new EvolutionToken();
        }

        try {
            $returnValue = $this->authenticationManager->authenticate($token);

            if ($returnValue instanceof TokenInterface) {
                return $this->securityContext->setToken($returnValue);
            }
        } catch (AuthenticationException $e) {
            $this->securityContext->setToken(null);

            if (null !== $this->logger) {
                $this->logger->info(sprintf('Authentication request failed: %s', $e->getMessage()));
            }
        }
    }
}
