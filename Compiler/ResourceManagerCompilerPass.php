<?php

namespace TechPromux\Bundle\BaseBundle\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;


class ResourceManagerCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('doctrine.orm.default_entity_manager')) {
            return;
        }

        // TODO take id from parameters?

        $entityManagerDefinitionId = 'doctrine.orm.default_entity_manager';

        $taggedServicesIds = $container->findTaggedServiceIds(
            'techpromux.resource_manager'
        );

        foreach ($taggedServicesIds as $id => $tags) {

            $serviceDefinition = $container->getDefinition($id);

            $serviceDefinition->addMethodCall(
                'setEntityManager', array(new \Symfony\Component\DependencyInjection\Reference($entityManagerDefinitionId))
            );

        }
    }

}
