<?php

namespace Theodo\Evolution\Bundle\SecurityBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('theodo_evolution_security');

        $rootNode
            ->children()
            ->scalarNode('user_repository')
                ->info('The service name of the repository that should be used to retrieve the user to authenticate')
                ->isRequired()
                ->end()
            ->scalarNode('user_transformer')
                ->info('The service name that transforms legacy user (i.e. sfGuardUser) into a UserInterface implementation')
                ->isRequired()
                ->end()
            ->scalarNode('algorithm')
                ->info('The algorithm to use with the callable password encoder')
                ->defaultValue('sha1')
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
