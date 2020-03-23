<?php

namespace test\eLife\Journal;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase as BaseKernelTestCase;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

abstract class KernelTestCase extends BaseKernelTestCase
{
    use AppKernelTestCase;

    final protected function alwaysGrantedAuthorizationChecker() : AuthorizationCheckerInterface
    {
        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authorizationChecker->method('isGranted')->willReturn(true);

        return $authorizationChecker;
    }
}
