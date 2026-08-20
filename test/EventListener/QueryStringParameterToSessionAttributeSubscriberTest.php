<?php

namespace test\eLife\Journal\EventListener;

use eLife\Journal\EventListener\QueryStringParameterToSessionAttributeSubscriber;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Traversable;

final class QueryStringParameterToSessionAttributeSubscriberTest extends TestCase
{
    #[Test]
    #[DataProvider('sessionAttributeProvider')]
    public function it_sets_a_session_attribute(string $uri, array $expected)
    {
        $subscriber = new QueryStringParameterToSessionAttributeSubscriber('parameter', 'attribute');

        $request = Request::create($uri);
        $request->setSession(new Session(new MockArraySessionStorage()));
        $event = new RequestEvent($this->createMock(HttpKernelInterface::class), $request, HttpKernelInterface::MASTER_REQUEST);

        $subscriber->onKernelRequest($event);

        $this->assertSame($expected, $request->getSession()->all());
    }

    public static function sessionAttributeProvider() : Traversable
    {
        yield 'no value' => ['?parameter', ['attribute' => '']];
        yield 'value' => ['?parameter=foo', ['attribute' => 'foo']];
        yield 'multiple parameters' => ['?foo=bar&parameter', ['attribute' => '']];
    }

    #[Test]
    public function it_ignores_other_parameters()
    {
        $subscriber = new QueryStringParameterToSessionAttributeSubscriber('parameter', 'attribute');

        $request = Request::create('?foo');
        $request->setSession(new Session(new MockArraySessionStorage()));
        $event = new RequestEvent($this->createMock(HttpKernelInterface::class), $request, HttpKernelInterface::SUB_REQUEST);

        $subscriber->onKernelRequest($event);

        $this->assertEmpty($request->getSession()->all());
    }

    #[Test]
    public function it_ignores_sub_requests()
    {
        $subscriber = new QueryStringParameterToSessionAttributeSubscriber('parameter', 'attribute');

        $request = Request::create('?parameter');
        $request->setSession(new Session(new MockArraySessionStorage()));
        $event = new RequestEvent($this->createMock(HttpKernelInterface::class), $request, HttpKernelInterface::SUB_REQUEST);

        $subscriber->onKernelRequest($event);

        $this->assertEmpty($request->getSession()->all());
    }
}
