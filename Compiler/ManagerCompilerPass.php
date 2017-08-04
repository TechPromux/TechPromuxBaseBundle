<?php

namespace TechPromux\BaseBundle\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;


class ManagerCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $entityManagerDefinitionId = 'doctrine.orm.default_entity_manager';
        $jmsSerializerDefinitionId = 'jms_serializer';
        $formFactoryDefinitionId = 'form.factory';
        $eventDispatcherDefinitionId = 'event_dispatcher';

        $taggedServicesIds = $container->findTaggedServiceIds(
            'techpromux.manager'
        );

        foreach ($taggedServicesIds as $id => $tags) {

            $serviceDefinition = $container->getDefinition($id);

            $serviceDefinition->addMethodCall(
                'setEntityManager',
                array(new \Symfony\Component\DependencyInjection\Reference($entityManagerDefinitionId))
            );
            $serviceDefinition->addMethodCall(
                'setJmsSerializer',
                array(new \Symfony\Component\DependencyInjection\Reference($jmsSerializerDefinitionId))
            );
            $serviceDefinition->addMethodCall(
                'setFormFactory',
                array(new \Symfony\Component\DependencyInjection\Reference($formFactoryDefinitionId))
            );
            $serviceDefinition->addMethodCall(
                'setEventDispatcher',
                array(new \Symfony\Component\DependencyInjection\Reference($eventDispatcherDefinitionId))
            );

        }
    }

}
