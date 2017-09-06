<?php

namespace eLife\Journal\Security\Authorization;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;

final class MissingCredentialsAuthorizationChecker implements AuthorizationCheckerInterface
{
    private $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public function isGranted($attributes, $object = null) : bool
    {
        try {
            return $this->authorizationChecker->isGranted($attributes, $object);
        } catch (AuthenticationCredentialsNotFoundException $e) {
            return false;
        }
    }
}
