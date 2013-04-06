<?php

namespace Theodo\Evolution\Bundle\SecurityBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Parser;
use Theodo\Evolution\Bundle\SecurityBundle\DependencyInjection\TheodoEvolutionSecurityExtension;

class TheodoEvolutionSecurityExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testSfGuardConfiguration()
    {
        $extension = new TheodoEvolutionSecurityExtension();

        $config = $this->getSfGuardConfiguration();
        $extension->load(array($config), new ContainerBuilder());
    }

    /**
     * Returns a sample configuration. It's great for documentational purposes.
     *
     * @TODO Replace by getMinimumConfiguration and getFullConfiguration
     */
    public function getSfGuardConfiguration()
    {
        $config = <<<YAML
legacy: sf_guard
sf_guard:
    algorithm: sha1
YAML;

        $parser = new Parser();

        return $parser->parse($config);
    }

    public function getSfGuardDefaults()
    {
        $config = <<<YAML
legacy: sf_guard
YAML;

        $parser = new Parser();

        return $parser->parse($config);
    }
}
