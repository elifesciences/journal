<?php

namespace test\eLife\Journal\Templating;

use eLife\Journal\Templating\PromiseAwareEngine;
use GuzzleHttp\Promise\FulfilledPromise;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\TwigEngine;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Templating\TemplateNameParser;
use Twig_Environment;
use Twig_Loader_Array;

final class PromiseAwareEngineTest extends TestCase
{
    /**
     * @test
     */
    public function it_is_a_templating_engine()
    {
        $twigLoader = new Twig_Loader_Array([]);
        $wrappedEngine = new TwigEngine(new Twig_Environment($twigLoader), new TemplateNameParser());

        $engine = new PromiseAwareEngine($wrappedEngine);

        $this->assertInstanceOf(EngineInterface::class, $engine);
    }

    /**
     * @test
     */
    public function it_unwraps_promises_before_rendering()
    {
        $twigLoader = new Twig_Loader_Array(['foo' => '{{bar}} {{qux}}']);
        $wrappedEngine = new TwigEngine(new Twig_Environment($twigLoader), new TemplateNameParser());

        $engine = new PromiseAwareEngine($wrappedEngine);

        $this->assertSame('baz quxx', $engine->render('foo', ['bar' => new FulfilledPromise('baz'), 'qux' => 'quxx']));
    }

    /**
     * @test
     */
    public function it_delegates_to_another_engine()
    {
        $twigLoader = new Twig_Loader_Array(['foo' => '{{bar}}']);
        $wrappedEngine = new TwigEngine(new Twig_Environment($twigLoader), new TemplateNameParser());

        $engine = new PromiseAwareEngine($wrappedEngine);

        $this->assertTrue($engine->exists('foo'));
        $this->assertFalse($engine->exists('bar'));
        $this->assertFalse($engine->supports('bar'));
    }
}
