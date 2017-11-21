<?php

namespace Kinulab\EfficientVoteBundle\Tests\Security;

use Kinulab\EfficientVoteBundle\Security\EfficientAccessDecisionManager;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class EfficientAccessDecisionManagerTest extends \PHPUnit\Framework\TestCase
{

    public function testEfficientAccessDecisionManagerAffirmative()
    {

        // affirmative : one 'yes' give access
        $EfficientADM = $this->getEfficientADM('affirmative');
        $token = new AnonymousToken(uniqid(), 'Homer');

        $this->assertTrue($EfficientADM->decide($token, ['ROLE_default_test']));
        $this->assertTrue($EfficientADM->decide($token, ['ROLE_test.1_blabla']));
        $this->assertFalse($EfficientADM->decide($token, ['ROLE_test.2_blabla']));
        $this->assertTrue($EfficientADM->decide($token, ['ROLE_test.3_blabla']));
        $this->assertFalse($EfficientADM->decide($token, ['SOMETHING_NOT_HANDLED']));
        $this->assertFalse($EfficientADM->decide($token, ['noUnderscoreRole']));

        $EfficientADM->setVoters([$this->getAffirmativeVoter()]);
        $this->assertTrue($EfficientADM->decide($token, ['ROLE_default_test']));
        $this->assertTrue($EfficientADM->decide($token, ['ROLE_test.1_blabla']));
        $this->assertTrue($EfficientADM->decide($token, ['ROLE_test.2_blabla']));
        $this->assertTrue($EfficientADM->decide($token, ['ROLE_test.3_blabla']));
        $this->assertTrue($EfficientADM->decide($token, ['SOMETHING_NOT_HANDLED']));
        $this->assertTrue($EfficientADM->decide($token, ['noUnderscoreRole']));

        $EfficientADM->setVoters([$this->getNegativeVoter()]);
        $this->assertTrue($EfficientADM->decide($token, ['ROLE_default_test']));
        $this->assertTrue($EfficientADM->decide($token, ['ROLE_test.1_blabla']));
        $this->assertFalse($EfficientADM->decide($token, ['ROLE_test.2_blabla']));
        $this->assertTrue($EfficientADM->decide($token, ['ROLE_test.3_blabla']));
        $this->assertFalse($EfficientADM->decide($token, ['SOMETHING_NOT_HANDLED']));
        $this->assertFalse($EfficientADM->decide($token, ['noUnderscoreRole']));
    }

    public function testEfficientAccessDecisionManagerConsensus()
    {

        // consensus : the majority win
        $EfficientADM = $this->getEfficientADM('consensus');
        $token = new AnonymousToken(uniqid(), 'Homer');

        $this->assertTrue($EfficientADM->decide($token, ['ROLE_default_test']));
        $this->assertTrue($EfficientADM->decide($token, ['ROLE_test.1_blabla']));
        $this->assertFalse($EfficientADM->decide($token, ['ROLE_test.2_blabla']));
        $this->assertTrue($EfficientADM->decide($token, ['ROLE_test.3_blabla']));
        $this->assertFalse($EfficientADM->decide($token, ['SOMETHING_NOT_HANDLED']));
        $this->assertFalse($EfficientADM->decide($token, ['noUnderscoreRole']));

        $EfficientADM->setVoters([$this->getAffirmativeVoter()]);
        $this->assertTrue($EfficientADM->decide($token, ['ROLE_default_test']));
        $this->assertTrue($EfficientADM->decide($token, ['ROLE_test.1_blabla']));
        $this->assertFalse($EfficientADM->decide($token, ['ROLE_test.2_blabla']));
        $this->assertTrue($EfficientADM->decide($token, ['ROLE_test.3_blabla']));
        $this->assertTrue($EfficientADM->decide($token, ['SOMETHING_NOT_HANDLED']));
        $this->assertTrue($EfficientADM->decide($token, ['noUnderscoreRole']));

        $EfficientADM->setVoters([$this->getNegativeVoter()]);
        $this->assertTrue($EfficientADM->decide($token, ['ROLE_default_test']));
        $this->assertTrue($EfficientADM->decide($token, ['ROLE_test.1_blabla']));
        $this->assertFalse($EfficientADM->decide($token, ['ROLE_test.2_blabla']));
        $this->assertFalse($EfficientADM->decide($token, ['ROLE_test.3_blabla']));
        $this->assertFalse($EfficientADM->decide($token, ['SOMETHING_NOT_HANDLED']));
        $this->assertFalse($EfficientADM->decide($token, ['noUnderscoreRole']));
    }

    public function testEfficientAccessDecisionManagerUnanimous()
    {

        // unanimous : everybody must agree
        $EfficientADM = $this->getEfficientADM('unanimous');
        $token = new AnonymousToken(uniqid(), 'Homer');

        $this->assertTrue($EfficientADM->decide($token, ['ROLE_default_test']));
        $this->assertTrue($EfficientADM->decide($token, ['ROLE_test.1_blabla']));
        $this->assertFalse($EfficientADM->decide($token, ['ROLE_test.2_blabla']));
        $this->assertFalse($EfficientADM->decide($token, ['ROLE_test.3_blabla']));
        $this->assertFalse($EfficientADM->decide($token, ['SOMETHING_NOT_HANDLED']));
        $this->assertFalse($EfficientADM->decide($token, ['noUnderscoreRole']));

        $EfficientADM->setVoters([$this->getAffirmativeVoter()]);
        $this->assertTrue($EfficientADM->decide($token, ['ROLE_default_test']));
        $this->assertTrue($EfficientADM->decide($token, ['ROLE_test.1_blabla']));
        $this->assertFalse($EfficientADM->decide($token, ['ROLE_test.2_blabla']));
        $this->assertFalse($EfficientADM->decide($token, ['ROLE_test.3_blabla']));
        $this->assertTrue($EfficientADM->decide($token, ['SOMETHING_NOT_HANDLED']));
        $this->assertTrue($EfficientADM->decide($token, ['noUnderscoreRole']));

        $EfficientADM->setVoters([$this->getNegativeVoter()]);
        $this->assertFalse($EfficientADM->decide($token, ['ROLE_default_test']));
        $this->assertFalse($EfficientADM->decide($token, ['ROLE_test.1_blabla']));
        $this->assertFalse($EfficientADM->decide($token, ['ROLE_test.2_blabla']));
        $this->assertFalse($EfficientADM->decide($token, ['ROLE_test.3_blabla']));
        $this->assertFalse($EfficientADM->decide($token, ['SOMETHING_NOT_HANDLED']));
        $this->assertFalse($EfficientADM->decide($token, ['noUnderscoreRole']));
    }

    /**
     * @return EfficientAccessDecisionManager
     */
    private function getEfficientADM($strategie)
    {
        $EfficientADM = new EfficientAccessDecisionManager([], $strategie);

        $EfficientADM->setEfficientVoters([
            'ROLE' => [
                'default' => [$this->getAffirmativeVoter()],
                'test.1' => [$this->getAffirmativeVoter(), $this->getAffirmativeVoter()],
                'test.2' => [$this->getNegativeVoter(), $this->getNegativeVoter()],
                'test.3' => [$this->getAffirmativeVoter(), $this->getNegativeVoter()],
            ],
        ]);

        return $EfficientADM;
    }

    private function getAffirmativeVoter()
    {
        return new class() {
            public function vote($token, $object, $attribut)
            {
                return VoterInterface::ACCESS_GRANTED;
            }
        };
    }

    private function getNegativeVoter()
    {
        return new class() {
            public function vote($token, $object, $attribut)
            {
                return VoterInterface::ACCESS_DENIED;
            }
        };
    }
}
