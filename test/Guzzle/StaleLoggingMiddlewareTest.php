<?php

namespace test\eLife\Journal\Guzzle;

use eLife\Journal\Guzzle\StaleLoggingMiddleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit_Framework_TestCase;
use Psr\Log\LoggerInterface;
use function GuzzleHttp\Promise\promise_for;

final class StaleLoggingMiddlewareTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_logs_if_the_response_is_stale()
    {
        $request = new Request('GET', 'http://www.example.com/');
        $options = [];
        $response = new Response(200, ['Age' => '10', 'Cache-Control' => 'max-age=5']);

        $logger = $this->createMock(LoggerInterface::class);

        $middleware = new StaleLoggingMiddleware($logger);
        $handler = function () use ($response) {
            return promise_for($response);
        };

        $promise = call_user_func($middleware($handler), $request, $options);

        $logger->expects($this->once())
            ->method('warning')
            ->with($this->identicalTo('Using stale response for GET http://www.example.com/'));

        $promise->wait();
    }

    /**
     * @test
     */
    public function it_does_not_log_if_the_response_is_fresh()
    {
        $request = new Request('GET', 'http://www.example.com/');
        $options = [];
        $response = new Response(200, ['Age' => '5', 'Cache-Control' => 'max-age=10']);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->never())->method($this->anything());

        $middleware = new StaleLoggingMiddleware($logger);
        $handler = function () use ($response) {
            return promise_for($response);
        };

        $promise = call_user_func($middleware($handler), $request, $options);
        $promise->wait();
    }

    /**
     * @test
     */
    public function it_does_not_log_if_the_response_is_stale_but_inside_the_stale_while_revalidate_time()
    {
        $request = new Request('GET', 'http://www.example.com/');
        $options = [];
        $response = new Response(200, ['Age' => '10', 'Cache-Control' => 'max-age=5, stale-while-revalidate=5']);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->never())->method($this->anything());

        $middleware = new StaleLoggingMiddleware($logger);
        $handler = function () use ($response) {
            return promise_for($response);
        };

        $promise = call_user_func($middleware($handler), $request, $options);
        $promise->wait();
    }
}
