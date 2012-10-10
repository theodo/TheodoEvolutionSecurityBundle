<?php
namespace Theodo\Evolution\SecurityBundle\Tests\Firewall\Listener\VendorSpecific;

use Theodo\Evolution\SecurityBundle\Tests\Firewall\Listener\BaseSecurityListenerTestCase;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * @author Marek Kalnik <marekk@theodo.fr>
 * @author Cyrille Jouineau <cyrillej@theodo.fr>
 *
 * @property $listener Theodo\Evolution\SecurityBundle\Firewall\Listener\VendorSpecific\Symfony14SecurityListener
 *
 * @todo: setter le username
 */
class Symfony14SecurityListenerTest extends BaseSecurityListenerTestCase
{
    public function setUp()
    {
        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');

        $kernel  = $this->getMockBuilder('Symfony\Component\HttpKernel\HttpKernelInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->event = new GetResponseEvent($kernel, $request, 'GET');

        $this->listener = $this->createListener('Theodo\Evolution\SecurityBundle\Firewall\Listener\VendorSpecific\Symfony14SecurityListener');
    }

    public function testUserRepositoryInjection()
    {
        $userRepository = $this->getMock('Theodo\Evolution\SecurityBundle\Repository\Symfony14UserRepositoryInterface');

        $this->listener->setUserRepository($userRepository);

        $this->assertEquals($userRepository, $this->listener->getUserRepository());
    }

    /**
     * @dataProvider createTokenUserDataProvider
     */
    public function testCreateToken($user, $username)
    {
        $userId = 1;

        $userRepository = $this->getMock('Theodo\Evolution\SecurityBundle\Repository\Symfony14UserRepositoryInterface');
        $userRepository->expects($this->once())
            ->method('findOneByUserId')
            ->with($userId)
            ->will($this->returnValue($user));

        $this->listener->setUserRepository($userRepository);

        $authBag = $this->getAuthBag();
        $authBag->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue(true));

        $attrBag = $this->getAttrBag();
        $attrBag->expects($this->once())
            ->method('get')
            ->with('sfGuardSecurityUser.user_id')
            ->will($this->returnValue($userId));
        $attrBag->expects($this->once())
            ->method('has')
            ->with('sfGuardSecurityUser.user_id')
            ->will($this->returnValue(true));

        $session = $this->getSession();
        $session->expects($this->any())
            ->method('getBag')
            ->will($this->returnValueMap(array(
                array(self::AUTH_NAMESPACE, $authBag),
                array(self::ATTR_NAMESPACE, $attrBag),
        )));

        $request = $this->event->getRequest();
        $request->expects($this->any())
            ->method('getSession')
            ->will($this->returnValue($session));
            
         $token = $this->listener->createToken($request);

         if ($username == null) {
            $this->assertNull($token);
         } else {
            $this->assertInstanceOf('Theodo\Evolution\SecurityBundle\Authentication\Token\EvolutionUserToken', $token);
            $this->assertEquals($username, $token->getUsername());
         }
    }


    public function createTokenUserDataProvider()
    {
        return array(
            array(new MockUser(), 'allomatch'),
            array(null, null),
        );
    }
}

class MockUser
{
    public function getUsername()
    {
        return 'allomatch';
    }
}