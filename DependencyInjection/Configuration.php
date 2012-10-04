<?php

namespace Theodo\Evolution\SecurityBundle\DependencyInjection;

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
                ->scalarNode('legacy')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->validate()
                        ->ifNotInArray($this->getSupportedLegacyTypes())
                        ->thenInvalid('Unsupported legacy security type.')
                    ->end()
                ->end()
            ->end()
        ;

        foreach ($this->getSupportedLegacyTypes() as $type) {
            call_user_func(array($this, 'add' . $this->convertToCamel($type) . 'Section'), $rootNode);
        }

        return $treeBuilder;
    }

    public function addSfGuardSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('sf_guard')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('algorithm')->defaultValue('sha1')->end()
                    ->end()
                ->end()
            ->end()
            ->beforeNormalization()
                ->ifTrue(function($v) { return !isset($v['legacy']) ||$v['legacy'] != 'sf_guard'; })
                ->then(function($v) { unset($v['sf_guard']); })
            ->end()
        ;
    }

    protected function getSupportedLegacyTypes()
    {
        return array('sf_guard');
    }

    private function convertToCamel($str)
    {
        $parts = explode('_', $str);
        $parts = $parts ? array_map('ucfirst', $parts) : array($str);

        return implode('', $parts);
    }
}
