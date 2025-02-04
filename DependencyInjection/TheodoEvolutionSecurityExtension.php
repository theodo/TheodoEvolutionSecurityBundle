<?php

namespace Theodo\Evolution\Bundle\SecurityBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class TheodoEvolutionSecurityExtension extends Extension
{
    /**
     * @var \Symfony\Component\DependencyInjection\Loader\YamlFileLoader
     */
    private $loader;

    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $this->loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config/services'));
        $this->loader->load('services.xml');

    }

    public function registerClasses($container, $configuration)
    {
        $legacy = $this->convertToCamel($configuration['legacy']);

        $container->setParameter(
            'theodo_evolution_security.user_provider.class',
            'Theodo\\Evolution\\SecurityBundle\\UserProvider\\' . $legacy . 'UserProvider'
        );

        $container->setParameter(
            'theodo_evolution_security.encoder.class',
            'Theodo\\Evolution\\SecurityBundle\\Encoder\\' . $legacy . 'PasswordEncoder'
        );

        $container->setParameter(
            'theodo_evolution_security.encoder.algorithm',
            $configuration[0]['sf_guard']['algorithm']
        );
    }

    private function convertToCamel($str)
    {
        $parts = explode('_', $str);
        $parts = $parts ? array_map('ucfirst', $parts) : array($str);

        return implode('', $parts);
    }
}
