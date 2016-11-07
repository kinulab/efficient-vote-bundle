<?php

namespace Kinulab\EfficientVoteBundle;

use Kinulab\EfficientVoteBundle\DependencyInjection\Compiler\VoterCompilerPass;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class KinulabEfficientVoteBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new VoterCompilerPass());
    }
}
