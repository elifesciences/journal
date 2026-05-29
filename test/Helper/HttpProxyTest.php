<?php

namespace test\eLife\Journal\Helper;

use eLife\Journal\Helper\HttpProxy;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use test\eLife\Journal\Providers;
use Traversable;

final class HttpProxyTest extends TestCase
{
    use Providers;

    private $mockResponses;
    private $httpProxy;

    protected function setUp()
    {
        $this->mockResponses = [];

        $mockClient = new MockHttpClient(function ($method, $url, $options) {
            return $this->mockResponses[$url] ?? new MockResponse('', ['http_code' => 404]);
        });

        $this->httpProxy = new HttpProxy($mockClient);
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
        $mp3 = __DIR__.'/../../assets/tests/blank.mp3';

        $this->mockResponses['http://www.example.com/test.mp3'] = new MockResponse(
            file_get_contents($mp3),
            ['http_code' => 200, 'response_headers' => ['Content-Type: audio/mp3']]
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
        $capturedHeaders = [];

        $mockClient = new MockHttpClient(function ($method, $url, $options) use (&$capturedHeaders) {
            $capturedHeaders = $options['headers'] ?? [];

            return new MockResponse('test', ['http_code' => 200, 'response_headers' => ['Content-Type: text/plain']]);
        });

        $httpProxy = new HttpProxy($mockClient);

        $request = HttpFoundationRequest::create('GET', 'http://www.example.com/', [], [], [], ['HTTP_X_FORWARDED_FOR' => '54.230.78.56, 34.197.12.171']);

        $response = $httpProxy->send($request, 'http://www.example.com/test.txt');

        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('test', $this->captureContent($response));

        $normalizedHeaders = array_change_key_case(array_column(array_map(function ($h) {
            [$k, $v] = explode(': ', $h, 2);
            return [$k, $v];
        }, $capturedHeaders), 1, 0));

        $this->assertSame('127.0.0.1', $normalizedHeaders['x-forwarded-for'] ?? '');
    }

    /**
     * @test
     */
    public function it_adds_x_forwarded_for_when_through_a_trusted_proxy()
    {
        HttpFoundationRequest::setTrustedProxies(['127.0.0.1'], HttpFoundationRequest::HEADER_X_FORWARDED_ALL);

        $capturedHeaders = [];

        $mockClient = new MockHttpClient(function ($method, $url, $options) use (&$capturedHeaders) {
            $capturedHeaders = $options['headers'] ?? [];

            return new MockResponse('test', ['http_code' => 200, 'response_headers' => ['Content-Type: text/plain']]);
        });

        $httpProxy = new HttpProxy($mockClient);

        $request = HttpFoundationRequest::create('GET', 'http://www.example.com/', [], [], [], ['HTTP_X_FORWARDED_FOR' => '54.230.78.56, 34.197.12.171']);

        $response = $httpProxy->send($request, 'http://www.example.com/test.txt');

        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('test', $this->captureContent($response));

        $normalizedHeaders = array_change_key_case(array_column(array_map(function ($h) {
            [$k, $v] = explode(': ', $h, 2);
            return [$k, $v];
        }, $capturedHeaders), 1, 0));

        $this->assertSame('54.230.78.56, 34.197.12.171, 127.0.0.1', $normalizedHeaders['x-forwarded-for'] ?? '');
    }

    /**
     * @test
     */
    public function it_returns_headers()
    {
        $this->mockResponses['http://www.example.com/test.txt'] = new MockResponse(
            'test',
            [
                'http_code' => 200,
                'response_headers' => [
                    'Cache-Control: public, max-age=100',
                    'Content-Length: 4',
                    'Content-Type: text/plain',
                    'Date: Wed, 21 Oct 2015 07:28:00 GMT',
                    'ETag: 1234567890',
                    'Expires: Wed, 21 Oct 2015 07:28:00 GMT',
                    'Last-Modified: Wed, 21 Oct 2015 07:28:00 GMT',
                    'Vary: Accept',
                ],
            ]
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
        $this->mockResponses['http://www.example.com/test.txt'] = new MockResponse(
            '',
            [
                'http_code' => 304,
                'response_headers' => [
                    'Cache-Control: public, max-age=300',
                    'Date: Wed, 21 Oct 2015 07:28:00 GMT',
                    'ETag: 1234567890',
                    'Expires: Wed, 21 Oct 2015 07:29:00 GMT',
                    'Vary: Accept',
                ],
            ]
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
        $this->mockResponses['http://www.example.com/test.txt'] = new MockResponse(
            '',
            ['http_code' => $fileStatusCode]
        );

        $request = HttpFoundationRequest::create('GET', 'http://www.example.com/');

        try {
            $this->httpProxy->send($request, 'http://www.example.com/test.txt');
            $this->fail('Expected HttpException to be thrown');
        } catch (HttpException $e) {
            $this->assertSame($expected, $e->getStatusCode());
        }
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
