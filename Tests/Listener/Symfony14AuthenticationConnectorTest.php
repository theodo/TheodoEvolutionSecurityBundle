<?php

namespace Theodo\Evolution\Bundle\SecurityBundle\Tests\Authentication\Listener;

use Theodo\Evolution\Bundle\SecurityBundle\Listener\Symfony14AuthenticationConnector;
use Theodo\Evolution\Bundle\SecurityBundle\Tests\BaseSecurityTestCase;

class Symfony14AuthenticationConnectorTest extends BaseSecurityTestCase
{
    public function testSetUser()
    {
        $authBag = $this->getAuthBag();
        $authBag->expects($this->once())
            ->method('set')
            ->with(true);

        $attrBag = $this->getAttrBag();
        $attrBag->expects($this->once())
            ->method('set')
            ->with('sfGuardSecurityUser.user_id');

        $session = $this->getSession();
        $session->expects($this->any())
            ->method('getBag')
            ->will(
                $this->returnValueMap(
                    array(
                        array(self::AUTH_NAMESPACE, $authBag),
                        array(self::ATTR_NAMESPACE, $attrBag),
                    )
                )
            );

        $this->runListener($session);
    }

    private function runListener($session)
    {
        $repository = $this->getMock('Theodo\Evolution\Bundle\SecurityBundle\Repository\Symfony14UserRepositoryInterface');
        $repository->expects($this->once())
            ->method('findOneByUsername')
            ->with('allomatch')
            ->will($this->returnValue(new AllomatchUser()));

        $token = $this->getMock('Theodo\Evolution\Bundle\SecurityBundle\Authentication\Token\EvolutionUserToken');
        $token->expects($this->once())
            ->method('getUser')
            ->will($this->returnValue('allomatch'));
        $token->expects($this->once())
            ->method('isAuthenticated')
            ->will($this->returnValue(true));

        $event = $this->getMockBuilder('Symfony\Component\Security\Core\Event\AuthenticationEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())
            ->method('getAuthenticationToken')
            ->will($this->returnValue($token));

        $listener = new Symfony14AuthenticationConnector($this->getBagManagerConfigurationMock(), $repository, $session);
        $listener->onSecurityAuthenticationSuccess($event);
    }
}

class AllomatchUser
{
    public function getUserId()
    {
        return 1;
    }
}

