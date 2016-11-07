<?php

namespace Kinulab\EfficientVoteBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class VoterCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $accessDecisionManager = $container->getDefinition('security.access.decision_manager');

        $efficient_voters = [];
        foreach ($container->findTaggedServiceIds('security.efficient_voter') as $id => $tags) {
            foreach ($tags as $attributes) {
                $type = isset($attributes['type']) ? $attributes['type'] : 'ROLE';
                $domain = isset($attributes['domain']) ? $attributes['domain'] : 'default';

                if (!isset($efficient_voters[$type])) {
                    $efficient_voters[$type] = [];
                }
                if (!isset($efficient_voters[$type][$domain])) {
                    $efficient_voters[$type][$domain] = [];
                }

                $efficient_voters[$type][$domain][] = new Reference($id);
            }
        }
        if (count($efficient_voters)) {
            $accessDecisionManager->addMethodCall('setEfficientVoters', [$efficient_voters]);
        }
    }
}
