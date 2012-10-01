<?php

namespace TheodoEvolution\SecurityBundle\Firewall\Listener;

use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use TheodoEvolution\SecurityBundle\Authentication\Token\EvolutionUserToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use TheodoEvolution\HttpFoundationBundle\Manager\VendorSpecific\Symfony10BagConfiguration;

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
     * @var null|\Symfony\Bridge\Monolog\Logger
     */
    protected $logger;

    /**
     * @param SecurityContextInterface       $securityContext
     * @param AuthenticationManagerInterface $authenticationManager
     * @param null|LoggerInterface           $logger
     */
    public function __construct(SecurityContextInterface $securityContext, AuthenticationManagerInterface $authenticationManager, LoggerInterface $logger = null)
    {
        $this->securityContext = $securityContext;
        $this->authenticationManager = $authenticationManager;
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
     * @return null|\TheodoEvolution\SecurityBundle\Authentication\Token\EvolutionUserToken
     */
    public function createToken(Request $request)
    {
        $namespaces = Symfony10BagConfiguration::getNamespaces();
        $authBag = $request->getSession()->getBag($namespaces[Symfony10BagConfiguration::AUTH_NAMESPACE]);
        $attributeBag = $request->getSession()->getBag($namespaces[Symfony10BagConfiguration::ATTRIBUTE_NAMESPACE]);

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
