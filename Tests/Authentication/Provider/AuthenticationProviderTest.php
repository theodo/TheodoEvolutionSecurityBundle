<?php

namespace TheodoEvolution\SecurityBundle\Tests\Authentication\Provider;

use TheodoEvolution\SecurityBundle\Authentication\Provider\AuthenticationProvider;
use TheodoEvolution\SecurityBundle\Authentication\Token\EvolutionUserToken;

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
            array($this->getMock('TheodoEvolution\SecurityBundle\Authentication\Token\EvolutionUserToken', array(), array(), '', false), true),
        );
    }

    /**
     * @dataProvider getInvalidTokens
     */
    public function testAuthenticateInvalidArgumentException($token)
    {
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

    /**
     * Return tokens that make the authentication provider
     * throwing an error.
     *
     * @return array
     */
    public function getInvalidTokens()
    {
        // A token with an unknown user
        $token = new EvolutionUserToken();
        $token->setUser('allomatch_user');

        return array(
            array($token),
        );
    }

    public function testAuthenticate()
    {
        $userProvider = $this->getUserProvider();
        $userProvider->expects($this->any())
            ->method('loadUserByUsername')
            ->with('allomatch_user')
            ->will($this->returnValue($this->getUser()));

        $this->provider = new AuthenticationProvider($userProvider);

        $token = new EvolutionUserToken();
        $token->setUser('allomatch_user');
        $token->setAttribute('is_authenticated', true);

        $authentictatedToken = $this->provider->authenticate($token);

        $this->assertFalse($token->isAuthenticated());
        $this->assertInstanceOf('TheodoEvolution\SecurityBundle\Authentication\Token\EvolutionUserToken', $authentictatedToken);
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
        $user = $this->getMockBuilder('TheodoEvolution\SecurityBundle\EvolutionUser')
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
     * @return \Theodo\EvolutionBundle\Security\Authentication\Token\EvolutionUserToken
     */
    protected function getToken($username, $attributes)
    {
        $token = new EvolutionUserToken();
        $token->setUser($username);
        $token->setAttributes($attributes);

        return $token;
    }
}
