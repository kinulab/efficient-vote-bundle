<?php

namespace Kinulab\EfficientVoteBundle\Tests\DependencyInjection;

use Kinulab\EfficientVoteBundle\DependencyInjection\KinulabEfficientVoteExtension;
use Kinulab\EfficientVoteBundle\DependencyInjection\Compiler\VoterCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DependencyInjectionTest extends \PHPUnit_Framework_TestCase
{
    /** @var KinulabEfficientVoteExtension $extension */
    private $extension;

    public function setUp()
    {
        parent::setUp();

        $this->extension = new KinulabEfficientVoteExtension();
    }

    public function testEfficientVotersAreInjectedByTypeAndDomain()
    {
        $container = $this->getContainer();
        $container
                ->register('security.access.decision_manager')
                ->setClass('Kinulab\EfficientVoteBundle\Security\EfficientAccessDecisionManager');

        $this->extension->load([], $container);

        $compilerPass = new VoterCompilerPass();
        $container
            ->register('test.efficient_voter')
            ->setClass('stdClass')
            ->addTag('security.efficient_voter', array('type' => 'test', 'domain' => 'test.1'));
        $container
            ->register('test.second_efficient_voter')
            ->setClass('stdClass')
            ->addTag('security.efficient_voter', array('type' => 'test', 'domain' => 'test.2'));
        $container
            ->register('test.third_efficient_voter')
            ->setClass('stdClass')
            ->addTag('security.efficient_voter', array('type' => 'test', 'domain' => 'test.2'));
        $container
            ->register('standard_voter')
            ->setClass('stdClass')
            ->addTag('security.voter');

        try {
            $compilerPass->process($container);
        } catch (\RuntimeException $e) {
            $this->fail('An expected exception has been raised.');
        }

        $efficientADM = $container->get('security.access.decision_manager');

        // Standard voter is registered
        $efficientVoters = new \ReflectionProperty($efficientADM, 'efficientVoters');
        $efficientVoters->setAccessible(true);
        $efficient_voters = $efficientVoters->getValue($efficientADM);

        // Efficient voters are registered and organized
        $this->assertCount(1, $efficient_voters);
        $this->assertArrayHasKey('test', $efficient_voters);
        $this->assertCount(2, $efficient_voters['test']);
        $this->assertArrayHasKey('test.1', $efficient_voters['test']);
        $this->assertArrayHasKey('test.2', $efficient_voters['test']);
        $this->assertCount(1, $efficient_voters['test']['test.1']);
        $this->assertCount(2, $efficient_voters['test']['test.2']);
    }

    public function testEfficientVotersAreInjectedWithDefaultValues()
    {
        $container = $this->getContainer();
        $container
                ->register('security.access.decision_manager')
                ->setClass('Kinulab\EfficientVoteBundle\Security\EfficientAccessDecisionManager');

        $this->extension->load([], $container);

        $compilerPass = new VoterCompilerPass();
        $container
            ->register('test.efficient_voter')
            ->setClass('stdClass')
            ->addTag('security.efficient_voter');

        try {
            $compilerPass->process($container);
        } catch (\RuntimeException $e) {
            $this->fail('An expected exception has been raised.');
        }

        $efficientADM = $container->get('security.access.decision_manager');

        $voters = new \ReflectionProperty($efficientADM, 'efficientVoters');
        $voters->setAccessible(true);
        $voters_services = $voters->getValue($efficientADM);

        $this->assertArrayHasKey('ROLE', $voters_services);
        $this->assertArrayHasKey('default', $voters_services['ROLE']);
        $this->assertCount(1, $voters_services['ROLE']['default']);
    }

    private function getContainer()
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.bundles', array(
            'KinulabEfficientVoteBundle' => true,
        ));
        $container->setParameter('kernel.cache_dir', '/tmp');
        $container->setParameter('kernel.debug', true);

        return $container;
    }
}
