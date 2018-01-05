<?php

namespace eLife\Journal\Security\Voter;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class RequestHeaderVoter extends Voter
{
    private $requestStack;
    private $role;
    private $header;
    private $headerValues;
    private $trustedProxy;

    public function __construct(RequestStack $requestStack, string $role, string $header, $headerValues, bool $trustedProxy = true)
    {
        $this->requestStack = $requestStack;
        $this->role = $role;
        $this->header = $header;
        $this->headerValues = (array) $headerValues;
        $this->trustedProxy = $trustedProxy;
    }

    protected function supports($attribute, $subject) : bool
    {
        return $this->role === $attribute;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token) : bool
    {
        $request = $this->requestStack->getMasterRequest();

        if ($this->trustedProxy && !$request->isFromTrustedProxy()) {
            return false;
        }

        if (!$request->headers->has($this->header)) {
            return false;
        }

        return !empty(array_intersect($request->headers->get($this->header, null, false), $this->headerValues));
    }
}
