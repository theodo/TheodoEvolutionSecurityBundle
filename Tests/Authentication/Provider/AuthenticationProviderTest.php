<?php

namespace Theodo\Evolution\Bundle\SecurityBundle\Tests\Authentication\Provider;

use Theodo\Evolution\Bundle\SecurityBundle\Authentication\Provider\AuthenticationProvider;
use Theodo\Evolution\Bundle\SecurityBundle\Authentication\Token\EvolutionToken;

/**
 * Class AuthenticationProviderTest description
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class AuthenticationProviderTest extends \PHPUnit_Framework_TestCase
{
    protected $provider;

    /**
     * @dataProvider getTokens
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     * @param bool                                                                 $expected
     */
    public function testSupports($token, $expected)
    {
        $this->provider = new AuthenticationProvider($this->getUserProvider());

        $this->assertEquals($expected, $this->provider->supports($token));
    }

    /**
     * @return array
     */
    public function getTokens()
    {
        return array(
            array($this->getMock('Symfony\Component\Security\Core\Authentication\Token\AnonymousToken', array(), array(), '', false), false),
            array($this->getMock('Theodo\Evolution\Bundle\SecurityBundle\Authentication\Token\EvolutionUserToken', array(), array(), '', false), true),
        );
    }

    public function testAuthenticateInvalidArgumentException()
    {
        $token = new EvolutionToken();
        $token->setUser('allomatch_user');

        $this->setExpectedException('Symfony\Component\Security\Core\Exception\UsernameNotFoundException', "No user with username \"{$token->getUsername()}\" found");

        $userProvider = $this->getUserProvider();
        $userProvider->expects($this->any())
            ->method('loadUserByUsername')
            ->with($token->getUsername())
            ->will($this->returnValueMap(array(
            array('allomatch_user', null),
            array('benjamin', true)
        )));

        $this->provider = new AuthenticationProvider($userProvider);

        $this->provider->authenticate($token);
    }

    public function testAuthenticate()
    {
        $userProvider = $this->getUserProvider();
        $userProvider->expects($this->any())
            ->method('loadUserByUsername')
            ->with('allomatch_user')
            ->will($this->returnValue($this->getUser()));

        $this->provider = new AuthenticationProvider($userProvider);

        $token = new EvolutionToken();
        $token->setUser('allomatch_user');
        $token->setAttribute('is_authenticated', true);

        $authentictatedToken = $this->provider->authenticate($token);

        $this->assertFalse($token->isAuthenticated());
        $this->assertInstanceOf('Theodo\Evolution\Bundle\SecurityBundle\Authentication\Token\EvolutionUserToken', $authentictatedToken);
        $this->assertTrue($authentictatedToken->isAuthenticated());
    }

    /**
     * @dataProvider getMockedTokens
     */
    public function testIsLegacyAuthenticated($token, $expected)
    {
        $this->provider = new AuthenticationProvider($this->getUserProvider());

        $this->assertEquals($expected, $this->provider->isLegacyAuthenticated($token));
    }

    /**
     * @return array
     */
    public function getMockedTokens()
    {
        return array(
            array($this->getToken('allomatch_user', array('is_authenticated' => true)), true),
            array($this->getToken('allomatch_user', array('is_authenticated' => false)), false),
            array($this->getToken('allomatch_user', array()), false),
        );
    }

    /**
     * Return a userprovider instance.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getUserProvider()
    {
        $userProvider = $this->getMockBuilder('Symfony\Component\Security\Core\User\UserProviderInterface')
            ->disableOriginalConstructor()
            ->getMock();

        return $userProvider;
    }

    /**
     * Return a EvolutionUser instance.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getUser()
    {
        $user = $this->getMockBuilder('Theodo\Evolution\Bundle\SecurityBundle\EvolutionUser')
            ->disableOriginalConstructor()
            ->setMethods(array('__toString', 'getRoles'))
            ->getMock();

        $user->expects($this->any())
            ->method('__toString')
            ->will($this->returnValue('allomatch_user'));

        $user->expects($this->any())
            ->method('getRoles')
            ->will($this->returnValue(array()));

        return $user;
    }

    /**
     * @param $username
     * @param $attributes
     *
     * @return EvolutionToken
     */
    protected function getToken($username, $attributes)
    {
        $token = new EvolutionToken();
        $token->setUser($username);
        $token->setAttributes($attributes);

        return $token;
    }
}
