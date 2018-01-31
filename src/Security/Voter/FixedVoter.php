<?php

namespace eLife\Journal\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class FixedVoter extends Voter
{
    private $role;
    private $vote;

    public function __construct(string $role, bool $vote)
    {
        $this->role = $role;
        $this->vote = $vote;
    }

    protected function supports($attribute, $subject) : bool
    {
        return $this->role === $attribute;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token) : bool
    {
        return $this->vote;
    }
}
