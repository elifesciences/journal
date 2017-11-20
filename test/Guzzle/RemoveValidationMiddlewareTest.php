<?php

namespace test\eLife\Journal\Guzzle;

use eLife\Journal\Guzzle\RemoveValidationMiddleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit_Framework_TestCase;
use Psr\Http\Message\RequestInterface;
use Traversable;
use function GuzzleHttp\Promise\promise_for;

final class RemoveValidationMiddlewareTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider searchQueryRequestProvider
     */
    public function it_removes_validation_headers_from_search_query_requests(RequestInterface $request)
    {
        $response = new Response(200, ['Etag' => '33a64df', 'Last-Modified' => 'Wed, 21 Oct 2015 07:28:00 GMT']);

        $handler = function () use ($response) {
            return promise_for($response);
        };

        $response = (new RemoveValidationMiddleware())($handler)($request, [])->wait();

        $this->assertFalse($response->hasHeader('Etag'));
        $this->assertFalse($response->hasHeader('Last-Modified'));
    }

    public function searchQueryRequestProvider() : Traversable
    {
        yield 'first parameter' => [new Request('GET', 'http://api.elifesciences.org/search?for=foo')];
        yield 'second parameter' => [new Request('GET', 'http://api.elifesciences.org/search?page=1&for=foo')];
    }

    /**
     * @test
     * @dataProvider otherRequestProvider
     */
    public function it_does_not_remove_validation_headers_from_other_requests(RequestInterface $request)
    {
        $response = new Response(200, ['Etag' => '33a64df', 'Last-Modified' => 'Wed, 21 Oct 2015 07:28:00 GMT']);

        $handler = function () use ($response) {
            return promise_for($response);
        };

        $response = (new RemoveValidationMiddleware())($handler)($request, [])->wait();

        $this->assertSame('33a64df', $response->getHeaderLine('Etag'));
        $this->assertSame('Wed, 21 Oct 2015 07:28:00 GMT', $response->getHeaderLine('Last-Modified'));
    }

    public function otherRequestProvider() : Traversable
    {
        yield 'different path' => [new Request('GET', 'http://api.elifesciences.org/foo')];
        yield 'no query' => [new Request('GET', 'http://api.elifesciences.org/search?for=')];
    }
}
