<?php

namespace eLife\Journal\Security\Voter;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class SessionAttributeVoter extends Voter
{
    private $requestStack;
    private $role;
    private $sessionAttribute;
    private $sessionAttributeValue;

    public function __construct(RequestStack $requestStack, string $role, string $sessionAttribute, $sessionAttributeValue = null)
    {
        $this->requestStack = $requestStack;
        $this->role = $role;
        $this->sessionAttribute = $sessionAttribute;
        $this->sessionAttributeValue = $sessionAttributeValue;
    }

    protected function supports($attribute, $subject) : bool
    {
        return $this->role === $attribute;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token) : bool
    {
        $session = $this->requestStack->getMasterRequest()->getSession();

        if (!$session || !$session->isStarted() || !$session->has($this->sessionAttribute)) {
            return false;
        }

        if (null === $this->sessionAttributeValue) {
            return true;
        }

        return $this->sessionAttributeValue === $session->get($this->sessionAttribute);
    }
}
