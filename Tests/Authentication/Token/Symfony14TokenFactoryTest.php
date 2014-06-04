<?php

namespace Theodo\Evolution\Bundle\SecurityBundle\Tests\Authentication\Token;

use Prophecy\PhpUnit\ProphecyTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Theodo\Evolution\Bundle\SecurityBundle\Authentication\Token\Symfony14TokenFactory;
use Theodo\Evolution\Bundle\SessionBundle\Attribute\ScalarBag;

/**
 * Symfony14TokenFactoryTest
 * 
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class Symfony14TokenFactoryTest extends ProphecyTestCase
{
    public function testShouldCreateAnEvolutionToken()
    {
        $factory = new Symfony14TokenFactory();
        $token = $factory->create($this->createGetResponseEvent(new Request()));

        $this->assertInstanceOf('Theodo\Evolution\Bundle\SecurityBundle\Authentication\Token\EvolutionToken', $token);
        $this->assertFalse($token->isAuthenticated());
    }

    /**
     * Creates a GetResponseEvent for tests purpose.
     *
     * @return GetResponseEvent
     */
    private function createGetResponseEvent(Request $request)
    {
        $kernel = $this->prophesize('Symfony\Component\HttpKernel\KernelInterface');

        return new GetResponseEvent($kernel->reveal(), $request, 1);
    }

    public function testShouldCreateAnAuthenticatedToken()
    {
        $bag = new ScalarBag('foo');
        $bag->set(true);

        $session = $this->prophesize('Symfony\Component\HttpFoundation\Session\SessionInterface');
        $session->getBag($bag->getName())->willReturn($bag);

        $request = new Request();
        $request->setSession($session->reveal());
        $factory = new Symfony14TokenFactory();
        $token = $factory->create($this->createGetResponseEvent($request));

        $this->assertInstanceOf('Theodo\Evolution\Bundle\SecurityBundle\Authentication\Token\EvolutionToken', $token);
        $this->assertTrue($token->isAuthenticated());
        $this->assertInstanceOf('Symfony\Component\Security\Core\User\UserInterface', $token->getUser());
    }
}
 