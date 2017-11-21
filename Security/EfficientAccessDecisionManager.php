<?php

namespace Kinulab\EfficientVoteBundle\Security;

use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManager;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Overwrite of symfony AccessDecisionManager to add efficient voters.
 */
class EfficientAccessDecisionManager extends AccessDecisionManager
{
    private $voters = [];
    private $standardVoters = [];
    private $efficientVoters = [];
    private $strategy;
    private $allowIfAllAbstainDecisions;
    private $allowIfEqualGrantedDeniedDecisions;

    /**
     * Constructor.
     *
     * @param VoterInterface[] $voters                             An array of VoterInterface instances
     * @param string           $strategy                           The vote strategy
     * @param bool             $allowIfAllAbstainDecisions         Whether to grant access if all voters abstained or not
     * @param bool             $allowIfEqualGrantedDeniedDecisions Whether to grant access if result are equals
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($voters = array(), $strategy = self::STRATEGY_AFFIRMATIVE, $allowIfAllAbstainDecisions = false, $allowIfEqualGrantedDeniedDecisions = true)
    {
        $strategyMethod = 'decide'.ucfirst($strategy);
        if (!is_callable(array($this, $strategyMethod))) {
            throw new \InvalidArgumentException(sprintf('The strategy "%s" is not supported.', $strategy));
        }

        $this->standardVoters = $voters;
        $this->strategy = $strategyMethod;
        $this->allowIfAllAbstainDecisions = (bool) $allowIfAllAbstainDecisions;
        $this->allowIfEqualGrantedDeniedDecisions = (bool) $allowIfEqualGrantedDeniedDecisions;
    }

    /**
     * Configures the voters.
     *
     * @param VoterInterface[] $voters An array of VoterInterface instances
     */
    public function setVoters(array $voters)
    {
        $this->standardVoters = $voters;
    }

    /**
     * Configures the efficient voters.
     *
     * @param VoterInterface[] $efficientVoters An array of VoterInterface instances
     */
    public function setEfficientVoters(array $efficientVoters)
    {
        $this->efficientVoters = $efficientVoters;
    }

    /**
     * {@inheritdoc}
     */
    public function decide(TokenInterface $token, array $attributes, $object = null)
    {
        $this->voters = $this->standardVoters;
        foreach ($attributes as $attribut) {
            if (!strpos($attribut, '_')) {
                continue;
            }
            list($type, $domain) = explode('_', $attribut);

            if (isset($this->efficientVoters[$type]) && isset($this->efficientVoters[$type][$domain])) {
                $this->voters = array_merge($this->voters, $this->efficientVoters[$type][$domain]);
            }
        }

        return $this->{$this->strategy}($token, $attributes, $object);
    }

    /**
     * Grants access if any voter returns an affirmative response.
     *
     * If all voters abstained from voting, the decision will be based on the
     * allowIfAllAbstainDecisions property value (defaults to false).
     */
    private function decideAffirmative(TokenInterface $token, array $attributes, $object = null)
    {
        $deny = 0;
        foreach ($this->voters as $voter) {
            $result = $voter->vote($token, $object, $attributes);
            switch ($result) {
                case VoterInterface::ACCESS_GRANTED:
                    return true;

                case VoterInterface::ACCESS_DENIED:
                    ++$deny;

                    break;

                default:
                    break;
            }
        }

        if ($deny > 0) {
            return false;
        }

        return $this->allowIfAllAbstainDecisions;
    }

    /**
     * Grants access if there is consensus of granted against denied responses.
     *
     * Consensus means majority-rule (ignoring abstains) rather than unanimous
     * agreement (ignoring abstains). If you require unanimity, see
     * UnanimousBased.
     *
     * If there were an equal number of grant and deny votes, the decision will
     * be based on the allowIfEqualGrantedDeniedDecisions property value
     * (defaults to true).
     *
     * If all voters abstained from voting, the decision will be based on the
     * allowIfAllAbstainDecisions property value (defaults to false).
     */
    private function decideConsensus(TokenInterface $token, array $attributes, $object = null)
    {
        $grant = 0;
        $deny = 0;
        foreach ($this->voters as $voter) {
            $result = $voter->vote($token, $object, $attributes);

            switch ($result) {
                case VoterInterface::ACCESS_GRANTED:
                    ++$grant;

                    break;

                case VoterInterface::ACCESS_DENIED:
                    ++$deny;

                    break;
            }
        }

        if ($grant > $deny) {
            return true;
        }

        if ($deny > $grant) {
            return false;
        }

        if ($grant > 0) {
            return $this->allowIfEqualGrantedDeniedDecisions;
        }

        return $this->allowIfAllAbstainDecisions;
    }

    /**
     * Grants access if only grant (or abstain) votes were received.
     *
     * If all voters abstained from voting, the decision will be based on the
     * allowIfAllAbstainDecisions property value (defaults to false).
     */
    private function decideUnanimous(TokenInterface $token, array $attributes, $object = null)
    {
        $grant = 0;
        foreach ($attributes as $attribute) {
            foreach ($this->voters as $voter) {
                $result = $voter->vote($token, $object, array($attribute));

                switch ($result) {
                    case VoterInterface::ACCESS_GRANTED:
                        ++$grant;

                        break;

                    case VoterInterface::ACCESS_DENIED:
                        return false;

                    default:
                        break;
                }
            }
        }

        // no deny votes
        if ($grant > 0) {
            return true;
        }

        return $this->allowIfAllAbstainDecisions;
    }
}
