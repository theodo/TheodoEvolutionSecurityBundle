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

        $this->loadSecurity($config, $container);
    }

    /**
     * Load security service definition file
     *
     * @param array            $config
     * @param ContainerBuilder $container
     */
    public function loadSecurity(array $config, ContainerBuilder $container)
    {
        $this->loader->load('security.yml');

        $entryPointClass = $container->getParameter('evolution.security.authentication.symfony'.$this->getVersion($container).'_entry_point.class');
        $container->getDefinition('evolution.security.authentication.entry_point')
            ->setClass($entryPointClass)
            ->replaceArgument(0, $config['login_path']);
    }

    /**
     * @param  \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @return mixed
     */
    private function getVersion(ContainerBuilder $container)
    {
        return $container->getParameter('evolution.legacy.version');
    }
}
