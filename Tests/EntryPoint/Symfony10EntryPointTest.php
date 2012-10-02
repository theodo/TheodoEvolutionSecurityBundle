<?php

namespace Theodo\Evolution\SecurityBundle\Tests\EntryPoint;

use Theodo\Evolution\SecurityBundle\EntryPoint\Symfony10EntryPoint;
use Theodo\Evolution\HttpFoundationBundle\Manager\Symfony10BagNamespaces;
use Symfony\Component\HttpFoundation\Session\Attribute\NamespacedAttributeBag;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpFoundation\Session\Session;
/**
 * Symfony10EntryPointTest class.
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class Symfony10EntryPointTest extends \PHPUnit_Framework_TestCase
{
    protected
        $entryPoint,
        $bag,
        $session,
        $storage;

    public function setUp()
    {
        $this->bag = new NamespacedAttributeBag(Symfony10BagNamespaces::ATTRIBUTE_NAMESPACE);
        $this->bag->setName(Symfony10BagNamespaces::ATTRIBUTE_NAMESPACE);

        $this->storage = new MockArraySessionStorage();
        $this->storage->registerBag($this->bag);

        $this->session = new Session($this->storage);

        $this->entryPoint = new Symfony10EntryPoint('/', $this->session);
    }

    public function testPrepareSession()
    {
        $request = $this->getMock('\\Symfony\\Component\\HttpFoundation\\Request');
        $request->expects($this->exactly(2))
            ->method('getUri')
            ->will($this->returnValue('/foo'));

        $this->entryPoint->prepareSession($request);

        $this->assertEquals('/foo', $this->bag->get('symfony/user/sfUser/attributes.signin_url'));
    }

    public function testStart()
    {
        $request = $this->getMock('\\Symfony\\Component\\HttpFoundation\\Request');
        $request->expects($this->exactly(2))
            ->method('getUri')
            ->will($this->returnValue('/foo'));

        $response = $this->entryPoint->start($request);

        $this->assertInstanceOf('\\Symfony\\Component\\HttpFoundation\\Response', $response);
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/', $response->getTargetUrl());
    }
}
