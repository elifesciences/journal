<?php

namespace test\eLife\Journal\Guzzle;

use eLife\Journal\Guzzle\StaleLoggingMiddleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Kevinrob\GuzzleCache\CacheMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use function GuzzleHttp\Promise\promise_for;

final class StaleLoggingMiddlewareTest extends TestCase
{
    /** @var LoggerInterface */
    private $logger;
    /** @var StaleLoggingMiddleware */
    private $middleware;

    public function setUp()
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->middleware = new StaleLoggingMiddleware($this->logger);
    }

    /**
     * @test
     */
    public function it_logs_if_the_response_is_stale()
    {
        $request = new Request('GET', 'http://www.example.com/');
        $options = [];
        $response = new Response(200, ['Age' => '10', CacheMiddleware::HEADER_CACHE_INFO => CacheMiddleware::HEADER_CACHE_STALE]);

        $handler = function () use ($response) {
            return promise_for($response);
        };

        $promise = call_user_func($this->middleware->__invoke($handler), $request, $options);

        $this->logger->expects($this->once())
            ->method('error')
            ->with($this->identicalTo('Using stale response for GET http://www.example.com/'));

        $promise->wait();
    }

    /**
     * @test
     */
    public function it_does_not_log_if_the_response_is_a_hit_that_is_fresh()
    {
        $request = new Request('GET', 'http://www.example.com/');
        $options = [];
        $response = new Response(200, [CacheMiddleware::HEADER_CACHE_INFO => CacheMiddleware::HEADER_CACHE_HIT]);

        $this->logger->expects($this->never())->method($this->anything());

        $handler = function () use ($response) {
            return promise_for($response);
        };

        $promise = call_user_func($this->middleware->__invoke($handler), $request, $options);
        $promise->wait();
    }

    /**
     * @test
     */
    public function it_does_not_log_if_the_response_is_a_miss_and_hence_is_fresh()
    {
        $request = new Request('GET', 'http://www.example.com/');
        $options = [];
        $response = new Response(200, [CacheMiddleware::HEADER_CACHE_INFO => CacheMiddleware::HEADER_CACHE_MISS]);

        $this->logger->expects($this->never())->method($this->anything());

        $handler = function () use ($response) {
            return promise_for($response);
        };

        $promise = call_user_func($this->middleware->__invoke($handler), $request, $options);
        $promise->wait();
    }

    /**
     * @test
     */
    public function it_logs_if_the_response_is_stale_but_inside_the_stale_while_revalidate_time()
    {
        $request = new Request('GET', 'http://www.example.com/');
        $options = [];
        $response = new Response(200, ['Age' => '10', 'Cache-Control' => 'max-age=5, stale-while-revalidate=5', CacheMiddleware::HEADER_CACHE_INFO => CacheMiddleware::HEADER_CACHE_STALE]);

        $handler = function () use ($response) {
            return promise_for($response);
        };

        $promise = call_user_func($this->middleware->__invoke($handler), $request, $options);

        $this->logger->expects($this->once())
            ->method('info')
            ->with($this->identicalTo('Using stale response for GET http://www.example.com/'));

        $promise->wait();
    }
}
