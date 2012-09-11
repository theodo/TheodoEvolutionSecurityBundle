<?php

namespace TheodoEvolution\SecurityBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use TheodoEvolution\SecurityBundle\Factory\EvolutionSecurityFactory;

class TheodoEvolutionSecurityBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new EvolutionSecurityFactory());
    }
}
