<?php

namespace Theodo\Evolution\Bundle\SecurityBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Theodo\Evolution\Bundle\SecurityBundle\Factory\EvolutionSecurityFactory;

class TheodoEvolutionSecurityBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new EvolutionSecurityFactory());
    }
}
