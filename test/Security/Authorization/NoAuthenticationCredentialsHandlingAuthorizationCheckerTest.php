<?php

namespace test\eLife\Journal\Security\Authorization;

use eLife\Journal\Security\Authorization\NoAuthenticationCredentialsHandlingAuthorizationChecker;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;

final class NoAuthenticationCredentialsHandlingAuthorizationCheckerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_grants_if_the_parent_grants()
    {
        $parent = $this->prophesize(AuthorizationCheckerInterface::class);
        $checker = new NoAuthenticationCredentialsHandlingAuthorizationChecker($parent->reveal());

        $parent->isGranted('attribute', 'object')->willReturn(true);

        $this->assertTrue($checker->isGranted('attribute', 'object'));
    }

    /**
     * @test
     */
    public function it_denies_if_the_parent_denies()
    {
        $parent = $this->prophesize(AuthorizationCheckerInterface::class);
        $checker = new NoAuthenticationCredentialsHandlingAuthorizationChecker($parent->reveal());

        $parent->isGranted('attribute', 'object')->willReturn(false);

        $this->assertFalse($checker->isGranted('attribute', 'object'));
    }

    /**
     * @test
     */
    public function it_denies_if_the_parent_does_not_have_credentials()
    {
        $parent = $this->prophesize(AuthorizationCheckerInterface::class);
        $checker = new NoAuthenticationCredentialsHandlingAuthorizationChecker($parent->reveal());

        $parent->isGranted('attribute', 'object')->willThrow(AuthenticationCredentialsNotFoundException::class);

        $this->assertFalse($checker->isGranted('attribute', 'object'));
    }
}
