<?php

namespace test\eLife\Journal\Twig;

use eLife\Journal\Twig\PatternExtension;
use eLife\Patterns\PatternRenderer\CallbackPatternRenderer;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\FlexibleViewModel;
use PHPUnit_Framework_TestCase;
use Twig_Environment;
use Twig_ExtensionInterface;
use Twig_Loader_Array;

final class PatternExtensionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_is_a_twig_extension()
    {
        $extension = new PatternExtension(new CallbackPatternRenderer(function (ViewModel $viewModel) : string {
            return 'foobar';
        }));

        $this->assertInstanceOf(Twig_ExtensionInterface::class, $extension);
    }

    /**
     * @test
     * @depends it_is_a_twig_extension
     */
    public function it_renders_patterns()
    {
        $twigLoader = new Twig_Loader_Array(['foo' => '{{render_pattern(bar)}}']);
        $twig = new Twig_Environment($twigLoader);
        $twig->addExtension(new PatternExtension(new CallbackPatternRenderer(function (ViewModel $viewModel) : string {
            return 'foobar';
        })));

        $this->assertSame('foobar', $twig->render('foo', ['bar' => new FlexibleViewModel('/foo', ['bar' => 'baz'])]));
    }
}
