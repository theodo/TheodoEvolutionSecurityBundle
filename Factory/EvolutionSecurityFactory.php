<?php

namespace Theodo\Evolution\SecurityBundle\Factory;

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

        return array($providerId, $listenerId, null);
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
