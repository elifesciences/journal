<?php

namespace eLife\Journal\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class TimeVoter extends Voter
{
    private $role;
    private $timestamp;

    public function __construct(string $role, int $timestamp)
    {
        $this->role = $role;
        $this->timestamp = $timestamp;
    }

    protected function supports($attribute, $subject) : bool
    {
        return $this->role === $attribute;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token) : bool
    {
        return time() >= $this->timestamp;
    }
}
