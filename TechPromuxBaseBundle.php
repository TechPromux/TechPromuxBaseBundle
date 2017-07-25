<?php

namespace TechPromux\Bundle\BaseBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use TechPromux\Bundle\BaseBundle\Compiler\ManagerCompilerPass;
use TechPromux\Bundle\BaseBundle\Compiler\ResourceManagerCompilerPass;

class TechPromuxBaseBundle extends Bundle
{
    public function build(ContainerBuilder $container) {
        parent::build($container);
        $container->addCompilerPass(new ManagerCompilerPass());
        $container->addCompilerPass(new ResourceManagerCompilerPass());
    }
}
