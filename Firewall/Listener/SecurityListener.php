<?php

namespace Theodo\Evolution\SecurityBundle\Firewall\Listener;

use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Theodo\Evolution\SecurityBundle\Authentication\Token\EvolutionUserToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Theodo\Evolution\HttpFoundationBundle\Manager\BagManagerConfigurationInterface;

/**
 * Class SecurityListener description
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class SecurityListener implements ListenerInterface
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
     * @var \Theodo\Evolution\HttpFoundationBundle\Manager\BagManagerConfigurationInterface;
     */
    protected $bagConfiguration;

    /**
     * @var null|\Symfony\Bridge\Monolog\Logger
     */
    protected $logger;

    /**
     * @param SecurityContextInterface       $securityContext
     * @param AuthenticationManagerInterface $authenticationManager
     * @param null|LoggerInterface           $logger
     */
    public function __construct(SecurityContextInterface $securityContext, AuthenticationManagerInterface $authenticationManager, BagManagerConfigurationInterface $bagConfiguration, LoggerInterface $logger = null)
    {
        $this->securityContext = $securityContext;
        $this->authenticationManager = $authenticationManager;
        $this->bagConfiguration = $bagConfiguration;
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
            if ($this->logger instanceof \Symfony\Component\HttpKernel\Log\LoggerInterface) {
                $this->logger->debug($e->getMessage());
            }
        }
    }

    /**
     * Create a user token.
     *
     * @param  \Symfony\Component\HttpFoundation\Request $request
     * @return null|\Theodo\Evolution\SecurityBundle\Authentication\Token\EvolutionUserToken
     */
    public function createToken(Request $request)
    {
        $authBag = $request->getSession()->getBag($this->bagConfiguration->getNamespace(BagManagerConfigurationInterface::AUTH_NAMESPACE));
        $attributeBag = $request->getSession()->getBag($this->bagConfiguration->getNamespace(BagManagerConfigurationInterface::ATTRIBUTE_NAMESPACE));

        // Set the user and the authentication status according to the legacy session.
        if (false == $authBag->getValue()
            || false == $attributeBag->has('sfGuardSecurityUser.username')
        ) {
            if (null !== $this->logger) {
                $this->logger->debug('The legacy user is not authenticated.');
            }

            if ($this->securityContext->getToken() instanceof EvolutionUserToken) {
                $this->setAnonymousToken();
            }

            return null;
        }

        // Create the token.
        $token = new EvolutionUserToken();
        $token->setUser($attributeBag->get('sfGuardSecurityUser.username'));
        $token->setAttribute('is_authenticated', $authBag->getValue());

        return $token;
    }

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
