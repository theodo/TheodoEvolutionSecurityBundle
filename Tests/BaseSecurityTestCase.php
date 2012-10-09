<?php

namespace Theodo\Evolution\SecurityBundle\Tests;

use Theodo\Evolution\HttpFoundationBundle\Manager\BagManagerConfigurationInterface;

/**
 * This class groups common methods for security testing.
 * Try to keep it simple don't add any slow setUp or tearDown functions.
 *
 * @author Marek Kalnik <marekk@theodo.fr>
 */
class BaseSecurityTestCase extends \PHPUnit_Framework_TestCase
{
    // Session namespaces used by mocked bags
    const AUTH_NAMESPACE = 'auth';
    const ATTR_NAMESPACE = 'attr';

    /**
     * Return a mocked scalar bag.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getAuthBag()
    {
        $authBag = $this->getMockBuilder('Theodo\Evolution\SessionBundle\ScalarBag')
                    ->disableOriginalConstructor()
                    ->setMethods(array('getValue', 'set'))
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
            ->setMethods(array('get', 'has', 'set'))
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
        $session = $this->getMockBuilder('Symfony\Component\HttpFoundation\Session\SessionInterface')
            ->disableOriginalConstructor()
            ->getMock();

        return $session;
    }

    /**
     * Mock BagManagerConfigurationInterface to use common namespaces in the whole test
     */
    protected function getBagManagerConfigurationMock()
    {
        $mock = $this->getMock('Theodo\Evolution\HttpFoundationBundle\Manager\BagManagerConfigurationInterface');
        $mock->expects($this->any())
            ->method('getNamespace')
            ->will(
                $this->returnValueMap(
                    array(
                        array(BagManagerConfigurationInterface::AUTH_NAMESPACE, self::AUTH_NAMESPACE),
                        array(BagManagerConfigurationInterface::ATTRIBUTE_NAMESPACE, self::ATTR_NAMESPACE),
                    )
                )
            );

        return $mock;
    }
}

