<?php

namespace Theodo\Evolution\SecurityBundle\Tests\Firewall\Listener;

use Theodo\Evolution\HttpFoundationBundle\Manager\BagManagerConfigurationInterface;

/**
 * @author Marek Kalnik <marekk@theodo.fr>
 */
class BaseSecurityListenerTestCase extends \PHPUnit_Framework_TestCase
{
    // Session namespaces used by mocked bags
    const AUTH_NAMESPACE = 'auth';
    const ATTR_NAMESPACE = 'attr';

    protected function createListener($class)
    {
        $am = $this->getMock('Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface', array(), array(), '', false);
        $adm = $this->getMock('Symfony\Component\Security\Core\Authorization\AccessDecisionManager', array(), array(), '', false);
        $sc = new \Symfony\Component\Security\Core\SecurityContext($am, $adm);
        $bm = $this->getBagManagerConfigurationMock();
        $logger = new \Symfony\Component\HttpKernel\Tests\Logger();

        return new $class($sc, $am, $bm, $logger);
    }


    /**
     * Mock BagManagerConfigurationInterface to use common namespaces in the whole test
     */
    protected function getBagManagerConfigurationMock()
    {
        $mock = $this->getMock('Theodo\Evolution\HttpFoundationBundle\Manager\BagManagerConfigurationInterface');
        $mock->expects($this->any())
            ->method('getNamespace')
            ->will($this->returnValueMap(array(
                array(BagManagerConfigurationInterface::AUTH_NAMESPACE, self::AUTH_NAMESPACE),
                array(BagManagerConfigurationInterface::ATTRIBUTE_NAMESPACE, self::ATTR_NAMESPACE),
            )));

        return $mock;
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

}