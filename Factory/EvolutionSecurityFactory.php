<?php

namespace TheodoEvolution\SecurityBundle\Factory;

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
        $providerId = $this->createAuthProviderId($container, $id, $config, $userProvider);

        $listenerId = $this->createListenerId($container, $id);

        $entryPoint = $this->createEntryPoint($container, $id, $config, $defaultEntryPoint);

        return array($providerId, $listenerId, $entryPoint);
    }

    public function createListenerId($container, $id)
    {
        $listenerId = 'evolution.security.authentication.listener.legacy.' . $id;
        $container->setDefinition($listenerId, new DefinitionDecorator('evolution.security.authentication.listener'));

        return $listenerId;
    }

    public function createAuthProviderId($container, $id, $config, $userProvider)
    {
        $providerId = 'evolution.security.authentication.provider.' . $id;
        $container
            ->setDefinition($providerId, new DefinitionDecorator('evolution.security.authentication.provider'))
            ->replaceArgument(0, new Reference($userProvider))
        ;

        return $providerId;
    }

    protected function createEntryPoint($container, $id, $config, $defaultEntryPoint)
    {
        $entryPointId = 'evolution.security.authentication.entry_point.'.$id;
        $container
            ->setDefinition($entryPointId, new DefinitionDecorator('evolution.security.authentication.entry_point'))
            ->addArgument($config['login_path'])
            ->addArgument(new Reference('session.storage.native'))
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
