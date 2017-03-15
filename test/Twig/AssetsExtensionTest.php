<?php

namespace test\eLife\Journal\Twig;

use ArrayObject;
use eLife\Journal\Twig\AssetsExtension;
use eLife\Patterns\HasAssets;
use PHPUnit_Framework_TestCase;
use Twig_Environment;
use Twig_ExtensionInterface;
use Twig_Loader_Array;

final class AssetsExtensionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_is_a_twig_extension()
    {
        $extension = new AssetsExtension($this->prophesize(HasAssets::class)->reveal());

        $this->assertInstanceOf(Twig_ExtensionInterface::class, $extension);
    }

    /**
     * @test
     * @depends it_is_a_twig_extension
     */
    public function it_adds_globals()
    {
        $hasAssets = $this->prophesize(HasAssets::class);
        $hasAssets->getStyleSheets()->willReturn($styleSheets = new ArrayObject(['/path/to/some/css']));
        $hasAssets->getJavaScripts()->willReturn($javaScripts = new ArrayObject(['path/to/some/js']));

        $twig = new Twig_Environment(new Twig_Loader_Array([]));
        $twig->addExtension(new AssetsExtension($hasAssets->reveal()));

        $globals = $twig->getGlobals();

        $this->assertEquals($globals['stylesheets'], ['assets/css/all.css']);
        $this->assertEquals($globals['javascripts'], ['assets/js/main.js']);
    }
}
