<?php
namespace Theodo\Evolution\Bundle\SecurityBundle\Tests\Firewall;

use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\User\User;
use Theodo\Evolution\Bundle\SecurityBundle\Authentication\Token\EvolutionToken;
use Theodo\Evolution\Bundle\SecurityBundle\Authentication\Token\TokenFactoryInterface;
use Theodo\Evolution\Bundle\SecurityBundle\Firewall\EvolutionSecurityListener;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Theodo\Evolution\Bundle\SessionBundle\Attribute\ScalarBag;
use Theodo\Evolution\Bundle\SessionBundle\Manager\BagManagerConfigurationInterface;
use Theodo\Evolution\Bundle\SessionBundle\Manager\Symfony1\BagConfiguration;

/**
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class EvolutionSecurityListenerTest extends ProphecyTestCase
{
    /**
     * @var Symfony14EvolutionSecurityListener
     */
    private $listener;

    /**
     * @var SecurityContextInterface
     */
    private $securityContext;

    /**
     * @var AuthenticationManagerInterface
     */
    private $authenticationManager;

    /**
     * @var TokenFactoryInterface
     */
    private $tokenFactory;

    public function setUp()
    {
        // Required to load Prophecy
        parent::setUp();

        $this->authenticationManager = $this->prophesize('Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface');
        $this->tokenFactory          = $this->prophesize('Theodo\Evolution\Bundle\SecurityBundle\Authentication\Token\TokenFactoryInterface');
        $accessDecisionManager = $this->prophesize('Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface');
        $userProvider          = $this->prophesize('Theodo\Evolution\Bundle\SecurityBundle\UserProvider\LegacyUserProviderInterface');

        // The security context is a value object, there is no need to mock it.
        $this->securityContext = new SecurityContext($this->authenticationManager->reveal(), $accessDecisionManager->reveal());

        $this->listener = new EvolutionSecurityListener(
            $this->securityContext,
            $this->authenticationManager->reveal(),
            $this->tokenFactory->reveal(),
            $userProvider->reveal()
        );
    }

    /**
     * If a token has been found in the session the AuthenticationManager
     * must revalidate that it is still valid checking that the user is
     * still authenticated with the symfony 1.4 "symfony/user/sfUser/authenticated"
     * session attribute.
     */
    public function testShouldAuthenticateATokenStoredInTheSecurityContext()
    {
        $event = $this->createGetResponseEvent();

        // The token is already authenticated and set in the security context
        $token = new EvolutionToken();
        $token->setAuthenticated(true);
        $token->setUser(new User('foo', 'bar'));
        $this->securityContext->setToken($token);

        // The authentication pass
        $this->authenticationManager->authenticate($token)->shouldBeCalled()->willReturnArgument();

        $this->listener->handle($event);
        $this->assertSuccessfulAuthentication($this->securityContext->getToken());
    }

    /**
     * Creates a GetResponseEvent for tests purpose.
     *
     * @return GetResponseEvent
     */
    private function createGetResponseEvent()
    {
        $kernel = $this->prophesize('Symfony\Component\HttpKernel\KernelInterface');

        return new GetResponseEvent($kernel->reveal(), new Request(), HttpKernelInterface::MASTER_REQUEST);
    }

    /**
     * Asserts that the security context has an authenticated EvolutionToken
     *
     * @param TokenInterface $token
     */
    private function assertSuccessfulAuthentication(TokenInterface $token)
    {
        $this->assertInstanceOf('Theodo\Evolution\Bundle\SecurityBundle\Authentication\Token\EvolutionToken', $token);
        $this->assertTrue($token->isAuthenticated());
        $this->assertInstanceOf('Symfony\Component\Security\Core\User\UserInterface', $token->getUser());
    }

    /**
     * If no token has been found in the security context, the token factory
     * should return an EvolutionToken to authenticate.
     */
    public function testShouldAttemptASuccessfulAuthentication()
    {
        $event = $this->createGetResponseEvent();

        $token = new EvolutionToken();
        $this->securityContext->setToken($token);

        $this->authenticationManager
            ->authenticate(Argument::type('Theodo\Evolution\Bundle\SecurityBundle\Authentication\Token\EvolutionToken'))
            ->will(function () use ($token) {
                $token->setAuthenticated(true);
                $token->setUser(new User('foo', 'bar'));

                return $token;
            })
        ;

        $this->listener->handle($event);
        $this->assertSuccessfulAuthentication($this->securityContext->getToken());
    }

    public function testShouldAttemptAFailingAuthentication()
    {
        $event = $this->createGetResponseEvent();

        $this->tokenFactory->create($event)->willThrow('\RuntimeException');
        $this->authenticationManager->authenticate(Argument::any())->shouldNotBeCalled();

        $this->listener->handle($event);

        $this->assertNull($this->securityContext->getToken());
    }
}
