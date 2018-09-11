<?php

namespace test\eLife\Journal\EventListener;

use eLife\Journal\EventListener\CacheControlSubscriber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Traversable;

final class CacheControlSubscriberTest extends TestCase
{
    /**
     * @test
     */
    public function it_adds_cache_headers_if_none_are_set()
    {
        $subscriber = new CacheControlSubscriber('public, max-age=1, stale-while-revalidate=2, stale-if-error=3');

        $subscriber->onKernelResponse(new FilterResponseEvent($this->createMock(HttpKernelInterface::class), new Request(), HttpKernelInterface::MASTER_REQUEST, $response = new Response('foo')));

        $this->assertSame('max-age=1, public, stale-if-error=3, stale-while-revalidate=2', $response->headers->get('Cache-Control'));
        $this->assertSame(['Cookie'], $response->getVary());
        $this->assertSame('"'.md5($response->getContent()).'"', $response->getEtag());
    }

    /**
     * @test
     */
    public function it_adds_a_vary_header()
    {
        $subscriber = new CacheControlSubscriber('public, max-age=1');

        $subscriber->onKernelResponse(new FilterResponseEvent($this->createMock(HttpKernelInterface::class), new Request(), HttpKernelInterface::MASTER_REQUEST, $response = new Response('foo', Response::HTTP_OK, ['Cache-Control' => 'public'])));

        $this->assertSame('public', $response->headers->get('Cache-Control'));
        $this->assertSame(['Cookie'], $response->getVary());
        $this->assertSame('"'.md5($response->getContent()).'"', $response->getEtag());
    }

    /**
     * @test
     * @dataProvider notFoundProvider
     */
    public function it_limits_404_cache_headers(string $cacheControl, string $expected, array $expectedVary = [])
    {
        $subscriber = new CacheControlSubscriber($cacheControl);

        $subscriber->onKernelResponse(new FilterResponseEvent($this->createMock(HttpKernelInterface::class), new Request(), HttpKernelInterface::MASTER_REQUEST, $response = new Response('foo', Response::HTTP_NOT_FOUND)));

        $this->assertSame($expected, $response->headers->get('Cache-Control'));
        $this->assertSame($expectedVary, $response->getVary());
        $this->assertSame('"'.md5($response->getContent()).'"', $response->getEtag());
    }

    public function notFoundProvider() : Traversable
    {
        yield 'cannot be cached' => ['private, no-cache, no-store, must-revalidate', 'must-revalidate, no-cache, no-store, private'];
        yield 'short max-age' => ['public, max-age=299', 'max-age=299, public', ['Cookie']];
        yield 'short s-mag-age' => ['public, max-age=298, s-maxage=299', 'max-age=298, public, s-maxage=299', ['Cookie']];
        yield 'long s-mag-age' => ['public, max-age=299, s-maxage=301', 'max-age=300, public', ['Cookie']];
        yield 'long max-age' => ['public, max-age=301', 'max-age=300, public', ['Cookie']];
        yield 'stale-while-revalidate' => ['public, max-age=299, stale-while-revalidate=1', 'max-age=299, public', ['Cookie']];
        yield 'stale-if-error' => ['public, max-age=299, stale-if-error=1', 'max-age=299, public, stale-if-error=1', ['Cookie']];
    }

    /**
     * @test
     */
    public function it_does_not_change_for_post_requests()
    {
        $subscriber = new CacheControlSubscriber('public, max-age=1');

        $subscriber->onKernelResponse(new FilterResponseEvent($this->createMock(HttpKernelInterface::class), Request::create('foo', Request::METHOD_POST), HttpKernelInterface::MASTER_REQUEST, $response = new Response('foo')));

        $this->assertSame('no-cache, private', $response->headers->get('Cache-Control'));
        $this->assertEmpty($response->getVary());
        $this->assertFalse($response->headers->has('Etag'));
    }

    /**
     * @test
     */
    public function it_does_not_change_for_streamed_responses()
    {
        $subscriber = new CacheControlSubscriber('public, max-age=1');

        $subscriber->onKernelResponse(new FilterResponseEvent($this->createMock(HttpKernelInterface::class), new Request(), HttpKernelInterface::MASTER_REQUEST, $response = new StreamedResponse()));

        $this->assertSame('no-cache, private', $response->headers->get('Cache-Control'));
        $this->assertEmpty($response->getVary());
        $this->assertFalse($response->headers->has('Etag'));
    }

    /**
     * @test
     */
    public function it_does_not_change_for_already_not_modified_responses()
    {
        $subscriber = new CacheControlSubscriber('public, max-age=1');

        $subscriber->onKernelResponse(new FilterResponseEvent($this->createMock(HttpKernelInterface::class), new Request(), HttpKernelInterface::MASTER_REQUEST, $response = new Response('', Response::HTTP_NOT_MODIFIED)));

        $this->assertSame('no-cache, private', $response->headers->get('Cache-Control'));
        $this->assertEmpty($response->getVary());
        $this->assertFalse($response->headers->has('Etag'));
    }

    /**
     * @test
     */
    public function it_does_not_change_when_there_is_a_new_session()
    {
        $subscriber = new CacheControlSubscriber('public, max-age=1');

        $request = new Request();
        $request->setSession($session = new Session(new MockArraySessionStorage()));
        $session->start();

        $subscriber->onKernelResponse(new FilterResponseEvent($this->createMock(HttpKernelInterface::class), $request, HttpKernelInterface::MASTER_REQUEST, $response = new Response()));

        $this->assertSame('no-cache, private', $response->headers->get('Cache-Control'));
        $this->assertEmpty($response->getVary());
        $this->assertFalse($response->headers->has('Etag'));
    }

    /**
     * @test
     */
    public function it_does_not_change_when_there_is_a_previous_session()
    {
        $subscriber = new CacheControlSubscriber('public, max-age=1');

        $request = new Request();
        $request->setSession($session = new Session(new MockArraySessionStorage()));
        $request->cookies->set($session->getName(), $session->getId());

        $subscriber->onKernelResponse(new FilterResponseEvent($this->createMock(HttpKernelInterface::class), $request, HttpKernelInterface::MASTER_REQUEST, $response = new Response()));

        $this->assertSame('no-cache, private', $response->headers->get('Cache-Control'));
        $this->assertEmpty($response->getVary());
        $this->assertFalse($response->headers->has('Etag'));
    }
}
