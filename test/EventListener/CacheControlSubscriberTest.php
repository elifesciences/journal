<?php

namespace test\eLife\Journal\EventListener;

use eLife\Journal\EventListener\CacheControlSubscriber;
use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class CacheControlSubscriberTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_adds_cache_headers_if_none_are_set()
    {
        $subscriber = new CacheControlSubscriber();

        $subscriber->onKernelResponse(new FilterResponseEvent($this->createMock(HttpKernelInterface::class), new Request(), HttpKernelInterface::MASTER_REQUEST, $response = new Response('foo')));

        $this->assertSame('max-age=300, public, stale-if-error=86400, stale-while-revalidate=300', $response->headers->get('Cache-Control'));
        $this->assertSame(['Cookie'], $response->getVary());
        $this->assertSame('"'.md5($response->getContent()).'"', $response->getEtag());
    }

    /**
     * @test
     */
    public function it_adds_a_vary_header()
    {
        $subscriber = new CacheControlSubscriber();

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
        $subscriber = new CacheControlSubscriber();

        $subscriber->onKernelResponse(new FilterResponseEvent($this->createMock(HttpKernelInterface::class), Request::create('foo', Request::METHOD_POST), HttpKernelInterface::MASTER_REQUEST, $response = new Response('foo')));

        $this->assertSame('no-cache', $response->headers->get('Cache-Control'));
        $this->assertEmpty($response->getVary());
        $this->assertFalse($response->headers->has('Etag'));
    }

    /**
     * @test
     */
    public function it_does_not_change_for_streamed_responses()
    {
        $subscriber = new CacheControlSubscriber();

        $subscriber->onKernelResponse(new FilterResponseEvent($this->createMock(HttpKernelInterface::class), new Request(), HttpKernelInterface::MASTER_REQUEST, $response = new StreamedResponse()));

        $this->assertSame('no-cache', $response->headers->get('Cache-Control'));
        $this->assertEmpty($response->getVary());
        $this->assertFalse($response->headers->has('Etag'));
    }

    /**
     * @test
     */
    public function it_does_not_change_for_already_not_modified_responses()
    {
        $subscriber = new CacheControlSubscriber();

        $subscriber->onKernelResponse(new FilterResponseEvent($this->createMock(HttpKernelInterface::class), new Request(), HttpKernelInterface::MASTER_REQUEST, $response = new Response('', Response::HTTP_NOT_MODIFIED)));

        $this->assertSame('no-cache', $response->headers->get('Cache-Control'));
        $this->assertEmpty($response->getVary());
        $this->assertFalse($response->headers->has('Etag'));
    }
}
