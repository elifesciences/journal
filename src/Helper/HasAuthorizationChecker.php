<?php

namespace eLife\Journal\Helper;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

trait HasAuthorizationChecker
{
    abstract protected function getAuthorizationChecker() : AuthorizationCheckerInterface;
}
