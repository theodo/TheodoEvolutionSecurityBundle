<?php

namespace Theodo\Evolution\SecurityBundle\Tests\Firewall;

use Theodo\Evolution\SecurityBundle\Firewall\Listener\SecurityListener;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Theodo\Evolution\HttpFoundationBundle\Manager\Symfony10BagNamespaces;

/**
 * Class SecurityListenerTest description
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class SecurityListenerTest extends \PHPUnit_Framework_TestCase
{
    protected $listener;

    protected $event;

    public function setUp()
    {
        $am = $this->getMock('Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface', array(), array(), '', false);
        $adm = $this->getMock('Symfony\Component\Security\Core\Authorization\AccessDecisionManager', array(), array(), '', false);
        $sc = new \Symfony\Component\Security\Core\SecurityContext($am, $adm);

        $logger = new \Symfony\Component\HttpKernel\Tests\Logger();

        $this->listener = new SecurityListener($sc, $am, $logger);

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
                array(Symfony10BagNamespaces::AUTH_NAMESPACE, $authBag),
                array(Symfony10BagNamespaces::ATTRIBUTE_NAMESPACE, $attrBag),
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
        $session->expects($this->exactly(2))
            ->method('getBag')
            ->will($this->returnValueMap(array(
                array(Symfony10BagNamespaces::AUTH_NAMESPACE, $authBag),
                array(Symfony10BagNamespaces::ATTRIBUTE_NAMESPACE, $attrBag),
        )));

        $request = $this->event->getRequest();
        $request->expects($this->once())
            ->method('hasSession')
            ->will($this->returnValue(true));

        $request->expects($this->exactly(2))
            ->method('getSession')
            ->will($this->returnValue($session));

        $am = $this->listener->getAuthenticationManager();
        $am->expects($this->once())
            ->method('authenticate')
            ->with($this->isInstanceOf('Theodo\Evolution\SecurityBundle\Authentication\Token\EvolutionUserToken'))
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
        $session->expects($this->exactly(2))
            ->method('getBag')
            ->will($this->returnValueMap(array(
                array(Symfony10BagNamespaces::AUTH_NAMESPACE, $authBag),
                array(Symfony10BagNamespaces::ATTRIBUTE_NAMESPACE, $attrBag),
        )));

        $request = $this->event->getRequest();
        $request->expects($this->once())
            ->method('hasSession')
            ->will($this->returnValue(true));

        $request->expects($this->exactly(2))
            ->method('getSession')
            ->will($this->returnValue($session));

        $am = $this->listener->getAuthenticationManager();
        $am->expects($this->once())
            ->method('authenticate')
            ->with($this->isInstanceOf('Theodo\Evolution\SecurityBundle\Authentication\Token\EvolutionUserToken'))
            ->will($this->returnCallback(function() {
                throw new \Symfony\Component\Security\Core\Exception\AuthenticationException('Exception thrown by AuthenticationProvider::authenticate method.');
            }));

        $this->listener->handle($this->event);

        $this->assertNull($this->event->getResponse());
        $this->assertNull($this->listener->getSecurityContext()->getToken());
        $this->assertContains('Exception thrown by AuthenticationProvider::authenticate method.', $this->listener->getLogger()->getLogs('debug'));
    }

    /**
     * Return a mocked Session instance.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getSession()
    {
        $session = $this->getMockBuilder('\Symfony\Component\HttpFoundation\Session\Storage\SessionStorage')
            ->disableOriginalConstructor()
            ->setMethods(array('getBag'))
            ->getMock();

        return $session;
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

    /**
     * Return a mocked Response instance.
     *
     * @param $code
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getResponse($code)
    {
        $response = $this->getMock('Symfony\Component\HttpFoundation\Response');
        $response->expects($this->any())
            ->method('getStatusCode')
            ->will($this->returnValue($code));

        return $response;
    }

    /**
     * Return a mocked scalar bag.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getAuthBag()
    {
        $authBag = $this->getMockBuilder('Theodo\Evolution\SessionBundle\ScalarBag')
                    ->disableOriginalConstructor()
                    ->setMethods(array('getValue'))
                    ->getMock();

        return $authBag;
    }

    /**
     * Return a mocked legacy attributes bag.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getAttrBag()
    {
        $attrBag = $this->getMockBuilder('Symfony\Component\HttpFoundation\Session\NamespacedAttributeBag')
            ->disableOriginalConstructor()
            ->setMethods(array('get', 'has'))
            ->getMock();

        return $attrBag;
    }
}
