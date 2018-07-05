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
