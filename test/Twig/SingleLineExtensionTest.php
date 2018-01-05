<?php

namespace test\eLife\Journal\Twig;

use eLife\Journal\Twig\SingleLineExtension;
use PHPUnit\Framework\TestCase;
use Twig_Environment;
use Twig_ExtensionInterface;
use Twig_Loader_Array;

final class SingleLineExtensionTest extends TestCase
{
    /**
     * @test
     */
    public function it_is_a_twig_extension()
    {
        $extension = new SingleLineExtension();

        $this->assertInstanceOf(Twig_ExtensionInterface::class, $extension);
    }

    /**
     * @test
     * @depends it_is_a_twig_extension
     */
    public function it_turns_strings_into_a_single_line()
    {
        $twigLoader = new Twig_Loader_Array(['foo' => '{{ string|single_line }}']);
        $twig = new Twig_Environment($twigLoader);
        $twig->addExtension(new SingleLineExtension());

        $this->assertSame('line one line two line three', $twig->render('foo', ['string' => "line one\nline two\n\nline three"]));
    }
}
