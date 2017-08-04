<?php

namespace TechPromux\BaseBundle\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;


class ResourceManagerCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $taggedServicesIds = $container->findTaggedServiceIds(
            'techpromux.resource_manager'
        );

        foreach ($taggedServicesIds as $id => $tags) {

            $serviceDefinition = $container->getDefinition($id);

            // TODO set custom options
        }
    }

}
