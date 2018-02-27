<?php

namespace Kinulab\EfficientVoteBundle\DependencyInjection;

use Kinulab\EfficientVoteBundle\Security\EfficientAccessDecisionManager;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class KinulabEfficientVoteExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $container->setParameter('security.access.decision_manager.class', EfficientAccessDecisionManager::class);
    }
}
