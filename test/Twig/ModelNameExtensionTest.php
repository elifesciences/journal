<?php

namespace test\eLife\Journal\Twig;

use eLife\Journal\Twig\ModelNameExtension;
use PHPUnit\Framework\TestCase;
use Twig_Environment;
use Twig_Error_Runtime;
use Twig_ExtensionInterface;
use Twig_Loader_Array;

final class ModelNameExtensionTest extends TestCase
{
    /**
     * @test
     */
    public function it_is_a_twig_extension()
    {
        $extension = new ModelNameExtension();

        $this->assertInstanceOf(Twig_ExtensionInterface::class, $extension);
    }

    /**
     * @test
     * @depends it_is_a_twig_extension
     */
    public function it_makes_model_names_readable()
    {
        $twigLoader = new Twig_Loader_Array(['foo' => '{{ model_name("research-article") }}']);
        $twig = new Twig_Environment($twigLoader);
        $twig->addExtension(new ModelNameExtension());

        $this->assertSame('Research Article', $twig->render('foo', []));
    }

    /**
     * @test
     * @depends it_is_a_twig_extension
     */
    public function it_fails_on_invalid_model_names()
    {
        $twigLoader = new Twig_Loader_Array(['foo' => '{{ model_name("foo") }}']);
        $twig = new Twig_Environment($twigLoader);
        $twig->addExtension(new ModelNameExtension());

        $this->expectException(Twig_Error_Runtime::class);

        $twig->render('foo', []);
    }
}
