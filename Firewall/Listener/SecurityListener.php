<?php

namespace Theodo\Evolution\Bundle\SecurityBundle\Firewall\Listener;

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
use Theodo\Evolution\Bundle\SecurityBundle\UserProvider\LegacyUserProviderInterface;
use Theodo\Evolution\Bundle\SessionBundle\Manager\BagManagerConfigurationInterface;
use Theodo\Evolution\Bundle\SecurityBundle\Authentication\Token\EvolutionUserToken;

/**
 * Class SecurityListener description
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
abstract class SecurityListener implements ListenerInterface
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
     * @var BagManagerConfigurationInterface;
     */
    protected $bagConfiguration;

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
     * @param BagManagerConfigurationInterface $bagConfiguration
     * @param LegacyUserProviderInterface      $provider
     * @param null|LoggerInterface             $logger
     */
    public function __construct(SecurityContextInterface $securityContext, AuthenticationManagerInterface $authenticationManager, BagManagerConfigurationInterface $bagConfiguration, LegacyUserProviderInterface $provider, LoggerInterface $logger = null)
    {
        $this->securityContext = $securityContext;
        $this->authenticationManager = $authenticationManager;
        $this->bagConfiguration = $bagConfiguration;
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
        $request = $event->getRequest();

        if (false == $request->hasSession()) {
            return;
        }

        $token = $this->createToken($request);

        if (!$token instanceof EvolutionUserToken) {
            return;
        }

        try {
            $returnValue = $this->authenticationManager->authenticate($token);

            if ($returnValue instanceof TokenInterface) {
                return $this->securityContext->setToken($returnValue);
            } elseif ($returnValue instanceof Response) {
                return $event->setResponse($returnValue);
            }
        } catch (AuthenticationException $e) {
            if (null !== $this->logger) {
                $this->logger->debug($e->getMessage());
            }
        }
    }

    /**
     * Create a user token.
     *
     * @param  \Symfony\Component\HttpFoundation\Request                                     $request
     * @return null|\Theodo\Evolution\Bundle\SecurityBundle\Authentication\Token\EvolutionUserToken
     */
    abstract protected function createToken(Request $request);

    /**
     * Set the security context token as anonymous.
     */
    public function setAnonymousToken()
    {
        if (null !== $this->logger) {
            $this->logger->info('Populated SecurityContext with an anonymous Token');
        }

        $this->securityContext->setToken(new AnonymousToken(time(), 'annon.', array()));
    }

    /**
     * @return \Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface
     */
    public function getAuthenticationManager()
    {
        return $this->authenticationManager;
    }

    /**
     * @return \Symfony\Component\Security\Core\SecurityContextInterface
     */
    public function getSecurityContext()
    {
        return $this->securityContext;
    }

    /**
     * @return null|\Symfony\Bridge\Monolog\Logger|\Symfony\Component\HttpKernel\Log\LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }
}
