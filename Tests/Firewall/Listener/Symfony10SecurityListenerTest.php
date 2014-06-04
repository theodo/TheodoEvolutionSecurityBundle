<?php

namespace Theodo\Evolution\Bundle\SecurityBundle\Tests\Firewall\Listener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Theodo\Evolution\Bundle\SessionBundle\Manager\BagManagerConfigurationInterface;
use Theodo\Evolution\Bundle\SecurityBundle\Firewall\Listener\Symfony10EvolutionSecurityListener as SecurityListener;
use Theodo\Evolution\Bundle\SecurityBundle\Tests\Firewall\Listener\BaseSecurityListenerTestCase;

/**
 * Class SecurityListenerTest description
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class Symfony10SecurityListenerTest extends BaseSecurityListenerTestCase
{
    protected $listener;

    protected $event;

    public function setUp()
    {
        $this->listener = $this->createListener('Theodo\Evolution\Bundle\SecurityBundle\Firewall\Listener\Symfony10SecurityListener');

        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');

        $kernel  = $this->getMockBuilder('Symfony\Component\HttpKernel\HttpKernelInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->event = new GetResponseEvent($kernel, $request, 'GET');
    }

    public function testHandleForbiddenWithoutSession()
    {
        $this->listener->handle($this->event);

        $this->assertNull($this->event->getResponse());
    }

    public function testHandleForbiddenWithSession()
    {
        $authBag = $this->getAuthBag();
        $authBag->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue(false));

        $attrBag = $this->getAttrBag();

        $session = $this->getMock('Symfony\Component\HttpFoundation\Session\Session');
        $session->expects($this->exactly(2))
            ->method('getBag')
            ->will($this->returnValueMap(array(
                array(self::AUTH_NAMESPACE, $authBag),
                array(self::ATTR_NAMESPACE, $attrBag),
        )));

        $request = $this->event->getRequest();
        $request->expects($this->once())
            ->method('hasSession')
            ->will($this->returnValue(true));

        $request->expects($this->exactly(2))
            ->method('getSession')
            ->will($this->returnValue($session));

        $this->listener->handle($this->event);

        $this->assertNull($this->event->getResponse());
    }

    public function testHandle()
    {
        $authBag = $this->getAuthBag();
        $authBag->expects($this->exactly(2))
            ->method('getValue')
            ->will($this->returnValue(true));

        $attrBag = $this->getAttrBag();
        $attrBag->expects($this->once())
            ->method('has')
            ->with('sfGuardSecurityUser.username')
            ->will($this->returnValue(true));
        $attrBag->expects($this->once())
            ->method('get')
            ->with('sfGuardSecurityUser.username')
            ->will($this->returnValue('allomatch'));

        $session = $this->getSession();
        $session->expects($this->exactly(3))
            ->method('getBag')
            ->will($this->returnValueMap(array(
                array(self::AUTH_NAMESPACE, $authBag),
                array(self::ATTR_NAMESPACE, $attrBag),
                array(self::ATTR_NAMESPACE, $attrBag),
        )));

        $request = $this->event->getRequest();
        $request->expects($this->once())
            ->method('hasSession')
            ->will($this->returnValue(true));

        $request->expects($this->exactly(3))
            ->method('getSession')
            ->will($this->returnValue($session));

        $am = $this->listener->getAuthenticationManager();
        $am->expects($this->once())
            ->method('authenticate')
            ->with($this->isInstanceOf('Theodo\Evolution\Bundle\SecurityBundle\Authentication\Token\EvolutionUserToken'))
            ->will($this->returnCallback(function($token) {
                $token->setAuthenticated(true);

                return $token;
            }));

        try {
            $this->listener->handle($this->event);
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }

        $this->assertNull($this->event->getResponse());
        $this->assertEquals('allomatch', $this->listener->getSecurityContext()->getToken()->getUser());
        $this->assertTrue($this->listener->getSecurityContext()->getToken()->isAuthenticated());
    }

    public function testHandleUnauthorized()
    {
        $authBag = $this->getAuthBag();
        $authBag->expects($this->exactly(2))
            ->method('getValue')
            ->will($this->returnValue(true));

        $attrBag = $this->getAttrBag();
        $attrBag->expects($this->once())
            ->method('has')
            ->with('sfGuardSecurityUser.username')
            ->will($this->returnValue(true));
        $attrBag->expects($this->once())
            ->method('get')
            ->with('sfGuardSecurityUser.username')
            ->will($this->returnValue('allomatch'));

        $session = $this->getSession();
        $session->expects($this->exactly(3))
            ->method('getBag')
            ->will($this->returnValueMap(array(
                array(self::AUTH_NAMESPACE, $authBag),
                array(self::ATTR_NAMESPACE, $attrBag),
                array(self::ATTR_NAMESPACE, $attrBag),
        )));

        $request = $this->event->getRequest();
        $request->expects($this->once())
            ->method('hasSession')
            ->will($this->returnValue(true));

        $request->expects($this->exactly(3))
            ->method('getSession')
            ->will($this->returnValue($session));

        $am = $this->listener->getAuthenticationManager();
        $am->expects($this->once())
            ->method('authenticate')
            ->with($this->isInstanceOf('Theodo\Evolution\Bundle\SecurityBundle\Authentication\Token\EvolutionUserToken'))
            ->will($this->returnCallback(function() {
                throw new \Symfony\Component\Security\Core\Exception\AuthenticationException('Exception thrown by AuthenticationProvider::authenticate method.');
            }));

        $this->listener->handle($this->event);

        $this->assertNull($this->event->getResponse());
        $this->assertNull($this->listener->getSecurityContext()->getToken());
        $this->assertContains('Exception thrown by AuthenticationProvider::authenticate method.', $this->listener->getLogger()->getLogs('debug'));
    }

    /**
     * Return a mocked token.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getAuthenticatedToken()
    {
        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');

        return $token;
    }
}
