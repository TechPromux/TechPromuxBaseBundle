<?php

namespace  TechPromux\BaseBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use  TechPromux\BaseBundle\Compiler\ManagerCompilerPass;
use  TechPromux\BaseBundle\Compiler\ResourceManagerCompilerPass;

class TechPromuxBaseBundle extends Bundle
{
    public function build(ContainerBuilder $container) {
        parent::build($container);
        $container->addCompilerPass(new ManagerCompilerPass());
        $container->addCompilerPass(new ResourceManagerCompilerPass());
    }
}
