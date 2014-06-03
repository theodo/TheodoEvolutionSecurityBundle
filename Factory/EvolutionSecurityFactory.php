<?php

namespace Theodo\Evolution\Bundle\SecurityBundle\Factory;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class EvolutionSecurityFactory description
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class EvolutionSecurityFactory implements SecurityFactoryInterface
{
    /**
     * @param ContainerBuilder $container
     * @param $id
     * @param $config
     * @param $userProvider
     * @param $defaultEntryPoint
     * @return array
     */
    public function create(ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint)
    {
        $providerId = $this->createAuthProviderId($container, $id, $userProvider);
        $listenerId = $this->createListenerId($container, $id);
        $entryPoint = $this->createEntryPoint($container, $id, $config);

        return array($providerId, $listenerId, $entryPoint);
    }

    /**
     * @param  ContainerBuilder $container
     * @param  string $id
     * @return string
     */
    private function createListenerId(ContainerBuilder $container, $id)
    {
        $listenerId = 'theodo_evolution_security.authentication.listener.' . $id;
        $container->setDefinition($listenerId, new DefinitionDecorator('theodo_evolution_security.authentication.listener'));

        return $listenerId;
    }

    /**
     * @param ContainerBuilder $container
     * @param $id
     * @param $userProvider
     * @return string
     */
    private function createAuthProviderId(ContainerBuilder $container, $id, $userProvider)
    {
        $providerId = 'theodo_evolution_security.authentication.provider.' . $id;
        $container
            ->setDefinition($providerId, new DefinitionDecorator('theodo_evolution_security.authentication.provider'))
            ->replaceArgument(0, new Reference($userProvider))
        ;

        return $providerId;
    }

    /**
     * @param ContainerBuilder $container
     * @param $id
     * @param $config
     * @return string
     */
    private function createEntryPoint(ContainerBuilder $container, $id, $config)
    {
        $entryPointId = 'theodo_evolution_security.authentication.legacy_entry_point.'.$id;
        $container
            ->setDefinition($entryPointId, new DefinitionDecorator('theodo_evolution_security.authentication.legacy_entry_point'))
            ->replaceArgument(0, $config['login_path'])
            ->replaceArgument(1, new Reference('session'))
        ;

        return $entryPointId;
    }

    public function getPosition()
    {
        return 'pre_auth';
    }

    public function getKey()
    {
        return 'evolution';
    }

    /**
     * @param \Symfony\Component\Config\Definition\Builder\NodeDefinition $builder
     */
    public function addConfiguration(NodeDefinition $builder)
    {
        $builder
            ->children()
                ->scalarNode('login_path')->cannotBeEmpty()->end()
            ->end()
        ;
    }
}
