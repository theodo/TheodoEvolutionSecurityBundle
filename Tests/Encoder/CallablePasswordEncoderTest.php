<?php

namespace Theodo\Evolution\Bundle\SecurityBundle\Tests\Encoder;

use Theodo\Evolution\Bundle\SecurityBundle\Encoder\CallablePasswordEncoder;

/**
 * Tests the Theodo\Evolution\Bundle\SecurityBundle\Encoder\CallablePasswordEncoder class
 */
class BasicPasswordEncoderTests extends \PHPUnit_Framework_TestCase
{
    /**
     * Test if correct interface is implemented
     */
    public function testInterface()
    {
        $encoder = new CallablePasswordEncoder('sha1');
        $this->assertInstanceOf('Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface', $encoder);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidAlgorithm()
    {
        $encoder = new CallablePasswordEncoder('☺ ');
    }

    public function testIsPasswordValid()
    {
        $encoder = new CallablePasswordEncoder('sha1');

        $encoded = sha1('salt' . 'pass');
        $this->assertTrue($encoder->isPasswordValid($encoded, 'pass', 'salt'));
        $this->assertFalse($encoder->isPasswordValid('☺ ', 'pass', 'salt'));
    }

    public function testReversed()
    {
        $encoder = new CallablePasswordEncoder('md5');

        $encoded = md5('salt' . 'pass');
        $this->assertEquals($encoded, $encoder->encodePassword('pass', 'salt'));

        $this->assertTrue($encoder->isPasswordValid($encoder->encodePassword('pass', 'salt'), 'pass', 'salt'));
    }
}
