<?php

namespace test\eLife\Journal\Templating;

use eLife\Journal\Templating\PromiseAwareEngine;
use GuzzleHttp\Promise\FulfilledPromise;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

final class PromiseAwareEngineTest extends TestCase
{
    #[Test]
    public function it_unwraps_promises_before_rendering()
    {
        $twig = new Environment(new ArrayLoader(['foo' => '{{bar}} {{qux}}']));

        $engine = new PromiseAwareEngine($twig);

        $this->assertSame('baz quxx', $engine->render('foo', ['bar' => new FulfilledPromise('baz'), 'qux' => 'quxx']));
    }
}
