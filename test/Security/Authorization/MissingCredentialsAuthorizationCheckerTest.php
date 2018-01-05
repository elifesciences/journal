<?php

namespace test\eLife\Journal\Security\Authorization;

use eLife\Journal\Security\Authorization\MissingCredentialsAuthorizationChecker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;

final class MissingCredentialsAuthorizationCheckerTest extends TestCase
{
    /**
     * @test
     */
    public function it_grants_if_the_parent_grants()
    {
        $parent = $this->prophesize(AuthorizationCheckerInterface::class);
        $checker = new MissingCredentialsAuthorizationChecker($parent->reveal());

        $parent->isGranted('attribute', 'object')->willReturn(true);

        $this->assertTrue($checker->isGranted('attribute', 'object'));
    }

    /**
     * @test
     */
    public function it_denies_if_the_parent_denies()
    {
        $parent = $this->prophesize(AuthorizationCheckerInterface::class);
        $checker = new MissingCredentialsAuthorizationChecker($parent->reveal());

        $parent->isGranted('attribute', 'object')->willReturn(false);

        $this->assertFalse($checker->isGranted('attribute', 'object'));
    }

    /**
     * @test
     */
    public function it_denies_if_the_parent_does_not_have_credentials()
    {
        $parent = $this->prophesize(AuthorizationCheckerInterface::class);
        $checker = new MissingCredentialsAuthorizationChecker($parent->reveal());

        $parent->isGranted('attribute', 'object')->willThrow(AuthenticationCredentialsNotFoundException::class);

        $this->assertFalse($checker->isGranted('attribute', 'object'));
    }
}
