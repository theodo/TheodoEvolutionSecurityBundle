<?php

namespace Theodo\Evolution\SecurityBundle\Tests\Firewall\Listener;

use Theodo\Evolution\SecurityBundle\Tests\BaseSecurityTestCase;

/**
 * @author Marek Kalnik <marekk@theodo.fr>
 */
abstract class BaseSecurityListenerTestCase extends BaseSecurityTestCase
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
}

