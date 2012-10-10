<?php

namespace Theodo\Evolution\SecurityBundle\DependencyInjection;

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

        $this->loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config/services'));

        $this->loader->load('security.yml');

        $this->registerClasses($container, $configs);
    }

    public function registerClasses($container, $configuration)
    {
        $legacy = $this->convertToCamel($configuration[0]['legacy']);

        $container->setParameter(
            'evolution.security.user_provider.class',
            'Theodo\\Evolution\\SecurityBundle\\UserProvider\\' . $legacy . 'UserProvider'
        );

        $container->setParameter(
            'evolution.security.encoder.class',
            'Theodo\\Evolution\\SecurityBundle\\Encoder\\' . $legacy . 'PasswordEncoder'
        );

        $container->setParameter(
            'theodo.evolution.security.encoder.algorithm',
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
