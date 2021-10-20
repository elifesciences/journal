<?php

namespace test\eLife\Journal\Twig;

use eLife\Journal\Security\HypothesisTokenGenerator;
use eLife\Journal\Twig\HypothesisTokenExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\User;
use Twig\Environment;
use Twig\Extension\ExtensionInterface;
use Twig\Loader\ArrayLoader;

final class HypothesisTokenExtensionTest extends TestCase
{
    /**
     * @test
     */
    public function it_is_a_twig_extension()
    {
        $extension = new HypothesisTokenExtension(new HypothesisTokenGenerator('authority', 'client_id', 'client_secret'));

        $this->assertInstanceOf(ExtensionInterface::class, $extension);
    }

    /**
     * @test
     * @depends it_is_a_twig_extension
     * @group time-sensitive
     */
    public function it_generates_a_token()
    {
        $tokenGenerator = new HypothesisTokenGenerator('authority', 'client_id', 'client_secret');

        $twigLoader = new ArrayLoader(['foo' => '{{ hypothesis_token(user) }}']);
        $twig = new Environment($twigLoader);
        $twig->addExtension(new HypothesisTokenExtension($tokenGenerator));

        $user = new User('username', 'password');

        $this->assertSame($tokenGenerator->generate($user), $twig->render('foo', ['user' => $user]));
    }
}
