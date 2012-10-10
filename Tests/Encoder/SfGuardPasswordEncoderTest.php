<?php

namespace Theodo\Evolution\SecurityBundle\Tests\Encoder;

use Theodo\Evolution\SecurityBundle\Encoder\SfGuardPasswordEncoder;

/**
 * Tests the Theodo\Evolution\SecurityBundle\Encoder\SfGuardPasswordEncoder class
 */
class SfGuardPasswordEncoderTests extends \PHPUnit_Framework_TestCase
{
    /**
     * Test if correct interface is implemented
     */
    public function testInterface()
    {
        $encoder = new SfGuardPasswordEncoder('sha1');
        $this->assertInstanceOf('Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface', $encoder);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidAlgorithm()
    {
        $encoder = new SfGuardPasswordEncoder('☺ ');
    }

    public function testIsPasswordValid()
    {
        $encoder = new SfGuardPasswordEncoder('sha1');

        $encoded = sha1('salt' . 'pass');
        $this->assertTrue($encoder->isPasswordValid($encoded, 'pass', 'salt'));
        $this->assertFalse($encoder->isPasswordValid('☺ ', 'pass', 'salt'));
    }

    public function testReversed()
    {
        $encoder = new SfGuardPasswordEncoder('md5');

        $encoded = md5('salt' . 'pass');
        $this->assertEquals($encoded, $encoder->encodePassword('pass', 'salt'));

        $this->assertTrue($encoder->isPasswordValid($encoder->encodePassword('pass', 'salt'), 'pass', 'salt'));
    }
}
