<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;
use Symfony\Component\HttpFoundation\StreamedResponse;
use test\eLife\Journal\Providers;
use test\eLife\Journal\WebTestCase;
use Traversable;

final class DownloadControllerTest extends WebTestCase
{
    use Providers;

    /**
     * @test
     */
    public function it_downloads_a_file()
    {
        $client = static::createClient();

        $this->mockApiResponse(
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
                fopen($mp3 = __DIR__.'/../../app/Resources/tests/blank.mp3', 'r')
            )
        );

        $content = $this->captureContent(function () use ($client) {
            $client->request('GET', $this->createDownloadUri('http://www.example.com/test.mp3'), [], [], ['HTTP_REFERER' => 'http://www.example.com/']);
        });

        $response = $client->getResponse();

        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([
            'cache-control' => ['no-cache'],
            'content-type' => ['audio/mp3'],
            'content-disposition' => ['attachment'],
        ], $response->headers->all());
        $this->assertSame(file_get_contents($mp3), $content);
    }

    /**
     * @test
     */
    public function it_sets_x_forwarded_for_when_not_through_a_trusted_proxy()
    {
        $client = static::createClient();

        $this->mockApiResponse(
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

        $content = $this->captureContent(function () use ($client) {
            $client->request('GET', $this->createDownloadUri('http://www.example.com/test.txt'), [], [], ['HTTP_X_FORWARDED_FOR' => '54.230.78.56, 34.197.12.171']);
        });

        $response = $client->getResponse();

        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([
            'cache-control' => ['no-cache'],
            'content-type' => ['text/plain; charset=UTF-8'],
            'content-disposition' => ['attachment'],
        ], $response->headers->all());
        $this->assertSame('test', $content);
    }

    /**
     * @test
     */
    public function it_adds_x_forwarded_for_when_through_a_trusted_proxy()
    {
        HttpFoundationRequest::setTrustedProxies(['127.0.0.1']);

        $client = static::createClient();

        $this->mockApiResponse(
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

        $content = $this->captureContent(function () use ($client) {
            $client->request('GET', $this->createDownloadUri('http://www.example.com/test.txt'), [], [], ['HTTP_X_FORWARDED_FOR' => '54.230.78.56, 34.197.12.171']);
        });

        $response = $client->getResponse();

        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([
            'cache-control' => ['no-cache'],
            'content-type' => ['text/plain; charset=UTF-8'],
            'content-disposition' => ['attachment'],
        ], $response->headers->all());
        $this->assertSame('test', $content);
    }

    /**
     * @test
     */
    public function it_follows_redirects()
    {
        $client = static::createClient();

        $this->mockApiResponse(
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

        $this->mockApiResponse(
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

        $content = $this->captureContent(function () use ($client) {
            $client->request('GET', $this->createDownloadUri('http://www.example.com/test.txt'));
        });

        $response = $client->getResponse();

        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([
            'cache-control' => ['no-cache'],
            'content-type' => ['text/plain; charset=UTF-8'],
            'content-disposition' => ['attachment'],
        ], $response->headers->all());
        $this->assertSame('test', $content);
    }

    /**
     * @test
     */
    public function it_downloads_a_file_with_a_name()
    {
        $client = static::createClient();

        $this->mockApiResponse(
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

        $content = $this->captureContent(function () use ($client) {
            $client->request('GET', $this->createDownloadUri('http://www.example.com/test.txt', 'foo.txt'));
        });

        $response = $client->getResponse();

        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([
            'cache-control' => ['no-cache'],
            'content-type' => ['text/plain; charset=UTF-8'],
            'content-disposition' => ['attachment; filename="foo.txt"'],
        ], $response->headers->all());
        $this->assertSame('test', $content);
    }

    /**
     * @test
     */
    public function it_returns_headers()
    {
        $client = static::createClient();

        $this->mockApiResponse(
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

        $content = $this->captureContent(function () use ($client) {
            $client->request('GET', $this->createDownloadUri('http://www.example.com/test.txt'));
        });

        $response = $client->getResponse();

        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([
            'cache-control' => ['max-age=100, public'],
            'content-length' => ['4'],
            'content-type' => ['text/plain; charset=UTF-8'],
            'date' => ['Wed, 21 Oct 2015 07:28:00 GMT'],
            'etag' => ['1234567890'],
            'expires' => ['Wed, 21 Oct 2015 07:28:00 GMT'],
            'last-modified' => ['Wed, 21 Oct 2015 07:28:00 GMT'],
            'vary' => ['Accept'],
            'content-disposition' => ['attachment'],
        ], $response->headers->all());
        $this->assertSame('test', $content);
    }

    /**
     * @test
     */
    public function it_respects_caching()
    {
        $client = static::createClient();

        $this->mockApiResponse(
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

        $client->request('GET', $this->createDownloadUri('http://www.example.com/test.txt'), [], [], ['HTTP_CACHE_CONTROL' => 'public', 'HTTP_IF_MODIFIED_SINCE' => 'Wed, 21 Oct 2015 07:28:00 GMT', 'HTTP_IF_NONE_MATCH' => '1234567890']);

        $response = $client->getResponse();

        $this->assertSame(304, $response->getStatusCode());
        $this->assertSame([
            'cache-control' => ['max-age=300, public'],
            'date' => ['Wed, 21 Oct 2015 07:28:00 GMT'],
            'etag' => ['1234567890'],
            'expires' => ['Wed, 21 Oct 2015 07:29:00 GMT'],
            'vary' => ['Accept'],
            'content-disposition' => ['attachment'],
        ], $response->headers->all());
        $this->assertEmpty($response->getContent());
    }

    /**
     * @test
     * @dataProvider statusCodeProvider
     */
    public function it_returns_other_status_codes(int $fileStatusCode, int $expected)
    {
        $client = static::createClient();

        $this->mockApiResponse(
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

        $client->request('GET', $this->createDownloadUri('http://www.example.com/test.txt'));

        $response = $client->getResponse();

        $this->assertSame($expected, $response->getStatusCode());
    }

    public function statusCodeProvider() : Traversable
    {
        return $this->arrayProvider([404 => 404, 410 => 410, 500 => 500, 503 => 500]);
    }

    private function createDownloadUri(string $fileUri, string $name = '') : string
    {
        $uri = 'http://localhost/download/'.base64_encode($fileUri);

        if ($name) {
            $uri .= "/$name";
        }

        return self::$kernel->getContainer()->get('uri_signer')->sign($uri);
    }

    private function captureContent(callable $callback) : string
    {
        ob_start();

        $callback();

        return ob_get_clean();
    }
}
