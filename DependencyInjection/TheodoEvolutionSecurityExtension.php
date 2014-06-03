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

        $container->setAlias('theodo_evolution_security.legacy_user_repository', $config['user_repository']);
        $container->setParameter('theodo_evolution_security.encoder.algorithm', $config['algorithm']);

        // @todo handle the case if the authentication listener parameter is a customer service id
        if (!empty($config['authentication_listener'])) {
            $authenticationlistenerClass = $container->getParameter('theodo_evolution_security.authentication.listener.'.$config['authentication_listener'].'.class');
            $container->setParameter('theodo_evolution_security.authentication.listener.class', $authenticationlistenerClass);
        }
    }
}
