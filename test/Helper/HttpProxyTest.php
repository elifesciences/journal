<?php

namespace test\eLife\Journal\Helper;

use Csa\Bundle\GuzzleBundle\GuzzleHttp\Middleware\MockMiddleware;
use eLife\Journal\Guzzle\NormalizingStorageAdapter;
use eLife\Journal\Helper\HttpProxy;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use test\eLife\Journal\InMemoryStorageAdapter;
use test\eLife\Journal\Providers;
use Traversable;

final class HttpProxyTest extends TestCase
{
    use Providers;

    private $httpProxy;
    private $storage;

    protected function setUp()
    {
        $this->storage = new NormalizingStorageAdapter(
            new InMemoryStorageAdapter(
                ['authorization', 'content-length', 'host', 'referer', 'user-agent', 'x-guzzle-cache']
            )
        );

        $stack = MockHandler::createWithMiddleware();
        $stack->push(new MockMiddleware($this->storage, 'replay'));

        $httpClient = new Client(['handler' => $stack, 'http_errors' => false]);

        $this->httpProxy = new HttpProxy($httpClient);
    }

    protected function tearDown()
    {
        HttpFoundationRequest::setTrustedProxies([], -1);
    }

    /**
     * @test
     */
    public function it_proxies_a_request()
    {
        $this->storage->save(
            new Request(
                'GET',
                'http://www.example.com/test.mp3',
                [
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Referer' => 'http://www.example.com/',
                    'X-Forwarded-For' => '127.0.0.1',
                    'X-Forwarded-Host' => 'localhost',
                    'X-Forwarded-Port' => '80',
                    'X-Forwarded-Proto' => 'http',
                ]
            ),
            new Response(
                200,
                ['Content-Type' => 'audio/mp3'],
                fopen($mp3 = __DIR__.'/../../assets/tests/blank.mp3', 'r')
            )
        );

        $request = HttpFoundationRequest::create('GET', 'http://www.example.com/');

        $response = $this->httpProxy->send($request, 'http://www.example.com/test.mp3');

        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertArraySubset([
            'content-type' => ['audio/mp3'],
        ], $response->headers->all());
        $this->assertSame(file_get_contents($mp3), $this->captureContent($response));
    }

    /**
     * @test
     */
    public function it_sets_x_forwarded_for_when_not_through_a_trusted_proxy()
    {
        $this->storage->save(
            new Request(
                'GET',
                'http://www.example.com/test.txt',
                [
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'X-Forwarded-For' => '127.0.0.1',
                    'X-Forwarded-Host' => 'localhost',
                    'X-Forwarded-Port' => '80',
                    'X-Forwarded-Proto' => 'http',
                ]
            ),
            new Response(
                200,
                ['Content-Type' => 'text/plain'],
                'test'
            )
        );

        $request = HttpFoundationRequest::create('GET', 'http://www.example.com/', [], [], [], ['HTTP_X_FORWARDED_FOR' => '54.230.78.56, 34.197.12.171']);

        $response = $this->httpProxy->send($request, 'http://www.example.com/test.txt');

        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertArraySubset([
            'content-type' => ['text/plain'],
        ], $response->headers->all());
        $this->assertSame('test', $this->captureContent($response));
    }

    /**
     * @test
     */
    public function it_adds_x_forwarded_for_when_through_a_trusted_proxy()
    {
        HttpFoundationRequest::setTrustedProxies(['127.0.0.1'], HttpFoundationRequest::HEADER_X_FORWARDED_ALL);

        $this->storage->save(
            new Request(
                'GET',
                'http://www.example.com/test.txt',
                [
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'X-Forwarded-For' => '54.230.78.56, 34.197.12.171, 127.0.0.1',
                    'X-Forwarded-Host' => 'localhost',
                    'X-Forwarded-Port' => '80',
                    'X-Forwarded-Proto' => 'http',
                ]
            ),
            new Response(
                200,
                ['Content-Type' => 'text/plain'],
                'test'
            )
        );

        $request = HttpFoundationRequest::create('GET', 'http://www.example.com/', [], [], [], ['HTTP_X_FORWARDED_FOR' => '54.230.78.56, 34.197.12.171']);

        $response = $this->httpProxy->send($request, 'http://www.example.com/test.txt');

        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertArraySubset([
            'content-type' => ['text/plain'],
        ], $response->headers->all());
        $this->assertSame('test', $this->captureContent($response));
    }

    /**
     * @test
     */
    public function it_follows_redirects()
    {
        $this->storage->save(
            new Request(
                'GET',
                'http://www.example.com/test.txt',
                [
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'X-Forwarded-For' => '127.0.0.1',
                    'X-Forwarded-Host' => 'localhost',
                    'X-Forwarded-Port' => '80',
                    'X-Forwarded-Proto' => 'http',
                ]
            ),
            new Response(
                301,
                ['Location' => 'http://www.example.com/foo.txt']
            )
        );

        $this->storage->save(
            new Request(
                'GET',
                'http://www.example.com/foo.txt',
                [
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'X-Forwarded-For' => '127.0.0.1',
                    'X-Forwarded-Host' => 'localhost',
                    'X-Forwarded-Port' => '80',
                    'X-Forwarded-Proto' => 'http',
                ]
            ),
            new Response(
                200,
                ['Content-Type' => 'text/plain'],
                'test'
            )
        );

        $request = HttpFoundationRequest::create('GET', 'http://www.example.com/');

        $response = $this->httpProxy->send($request, 'http://www.example.com/test.txt');

        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertArraySubset([
            'content-type' => ['text/plain'],
        ], $response->headers->all());
        $this->assertSame('test', $this->captureContent($response));
    }

    /**
     * @test
     */
    public function it_returns_headers()
    {
        $this->storage->save(
            new Request(
                'GET',
                'http://www.example.com/test.txt',
                [
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'X-Forwarded-For' => '127.0.0.1',
                    'X-Forwarded-Host' => 'localhost',
                    'X-Forwarded-Port' => '80',
                    'X-Forwarded-Proto' => 'http',
                ]
            ),
            new Response(
                200,
                [
                    'Cache-Control' => 'public, max-age=100',
                    'Content-Length' => 4,
                    'Content-Type' => 'text/plain',
                    'Date' => 'Wed, 21 Oct 2015 07:28:00 GMT',
                    'ETag' => '1234567890',
                    'Expires' => 'Wed, 21 Oct 2015 07:28:00 GMT',
                    'Last-Modified' => 'Wed, 21 Oct 2015 07:28:00 GMT',
                    'Vary' => 'Accept',
                ],
                'test'
            )
        );

        $request = HttpFoundationRequest::create('GET', 'http://www.example.com/');

        $response = $this->httpProxy->send($request, 'http://www.example.com/test.txt');

        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertArraySubset([
            'cache-control' => ['max-age=100, public'],
            'content-length' => ['4'],
            'content-type' => ['text/plain'],
            'date' => ['Wed, 21 Oct 2015 07:28:00 GMT'],
            'etag' => ['1234567890'],
            'expires' => ['Wed, 21 Oct 2015 07:28:00 GMT'],
            'last-modified' => ['Wed, 21 Oct 2015 07:28:00 GMT'],
            'vary' => ['Accept'],
        ], $response->headers->all());
        $this->assertSame('test', $this->captureContent($response));
    }

    /**
     * @test
     */
    public function it_respects_caching()
    {
        $this->storage->save(
            new Request(
                'GET',
                'http://www.example.com/test.txt',
                [
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Cache-Control' => 'public',
                    'If-Modified-Since' => 'Wed, 21 Oct 2015 07:28:00 GMT',
                    'If-None-Match' => '1234567890',
                    'X-Forwarded-For' => '127.0.0.1',
                    'X-Forwarded-Host' => 'localhost',
                    'X-Forwarded-Port' => '80',
                    'X-Forwarded-Proto' => 'http',
                ]
            ),
            new Response(
                304,
                [
                    'Cache-Control' => 'public, max-age=300',
                    'Date' => 'Wed, 21 Oct 2015 07:28:00 GMT',
                    'ETag' => '1234567890',
                    'Expires' => 'Wed, 21 Oct 2015 07:29:00 GMT',
                    'Vary' => 'Accept',
                ]
            )
        );

        $request = HttpFoundationRequest::create('GET', 'http://www.example.com/', [], [], [], ['HTTP_CACHE_CONTROL' => 'public', 'HTTP_IF_MODIFIED_SINCE' => 'Wed, 21 Oct 2015 07:28:00 GMT', 'HTTP_IF_NONE_MATCH' => '1234567890']);

        $response = $this->httpProxy->send($request, 'http://www.example.com/test.txt');

        $this->assertSame(304, $response->getStatusCode());
        $this->assertArraySubset([
            'cache-control' => ['max-age=300, public'],
            'date' => ['Wed, 21 Oct 2015 07:28:00 GMT'],
            'etag' => ['1234567890'],
            'expires' => ['Wed, 21 Oct 2015 07:29:00 GMT'],
            'vary' => ['Accept'],
        ], $response->headers->all());
        $this->assertEmpty($response->getContent());
    }

    /**
     * @test
     * @dataProvider statusCodeProvider
     */
    public function it_returns_other_status_codes(int $fileStatusCode, int $expected)
    {
        $this->storage->save(
            new Request(
                'GET',
                'http://www.example.com/test.txt',
                [
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'X-Forwarded-For' => '127.0.0.1',
                    'X-Forwarded-Host' => 'localhost',
                    'X-Forwarded-Port' => '80',
                    'X-Forwarded-Proto' => 'http',
                ]
            ),
            new Response($fileStatusCode)
        );

        $request = HttpFoundationRequest::create('GET', 'http://www.example.com/');

        $response = $this->httpProxy->send($request, 'http://www.example.com/test.txt');

        $this->assertSame($expected, $response->getStatusCode());
    }

    public function statusCodeProvider() : Traversable
    {
        return $this->arrayProvider([404 => 404, 410 => 410, 500 => 502, 503 => 502]);
    }

    private function captureContent(HttpFoundationResponse $response) : string
    {
        ob_start();

        $response->sendContent();

        return ob_get_clean();
    }
}
