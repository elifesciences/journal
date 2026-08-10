<?php

namespace test\eLife\Journal\Twig;

use eLife\Journal\Twig\ModelNameExtension;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Error\RuntimeError;
use Twig\Extension\ExtensionInterface;
use Twig\Loader\ArrayLoader;

final class ModelNameExtensionTest extends TestCase
{
    #[Test]
    public function it_is_a_twig_extension()
    {
        $extension = new ModelNameExtension();

        $this->assertInstanceOf(ExtensionInterface::class, $extension);
    }

    #[Test]
    #[Depends('it_is_a_twig_extension')]
    public function it_makes_model_names_readable()
    {
        $twigLoader = new ArrayLoader(['foo' => '{{ model_name("research-article") }}']);
        $twig = new Environment($twigLoader);
        $twig->addExtension(new ModelNameExtension());

        $this->assertSame('Research Article', $twig->render('foo', []));
    }

    #[Test]
    #[Depends('it_is_a_twig_extension')]
    public function it_fails_on_invalid_model_names()
    {
        $twigLoader = new ArrayLoader(['foo' => '{{ model_name("foo") }}']);
        $twig = new Environment($twigLoader);
        $twig->addExtension(new ModelNameExtension());

        $this->expectException(RuntimeError::class);

        $twig->render('foo', []);
    }
}
