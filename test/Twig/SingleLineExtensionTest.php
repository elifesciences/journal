<?php

namespace test\eLife\Journal\Twig;

use eLife\Journal\Twig\SingleLineExtension;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Extension\ExtensionInterface;
use Twig\Loader\ArrayLoader;

final class SingleLineExtensionTest extends TestCase
{
    /**
     * @test
     */
    public function it_is_a_twig_extension()
    {
        $extension = new SingleLineExtension();

        $this->assertInstanceOf(ExtensionInterface::class, $extension);
    }

    /**
     * @test
     * @depends it_is_a_twig_extension
     */
    public function it_turns_strings_into_a_single_line()
    {
        $twigLoader = new ArrayLoader(['foo' => '{{ string|single_line }}']);
        $twig = new Environment($twigLoader);
        $twig->addExtension(new SingleLineExtension());

        $this->assertSame('line one line two line three', $twig->render('foo', ['string' => "line one\nline two\n\nline three"]));
    }
}
