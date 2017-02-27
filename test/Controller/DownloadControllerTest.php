<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
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
                'http://www.example.com/test.txt'
            ),
            new Response(
                200,
                ['Content-Type' => 'text/plain'],
                'test'
            )
        );

        ob_start();
        $client->request('GET', $this->createDownloadUri('http://www.example.com/test.txt'));
        $content = ob_get_clean();

        $response = $client->getResponse();

        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([
            'content-type' => ['text/plain; charset=UTF-8'],
            'cache-control' => ['no-cache'],
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
                'http://www.example.com/test.txt'
            ),
            new Response(
                200,
                ['Content-Type' => 'text/plain'],
                'test'
            )
        );

        ob_start();
        $client->request('GET', $this->createDownloadUri('http://www.example.com/test.txt', 'foo.txt'));
        $content = ob_get_clean();

        $response = $client->getResponse();

        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([
            'content-type' => ['text/plain; charset=UTF-8'],
            'cache-control' => ['no-cache'],
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
                'http://www.example.com/test.txt'
            ),
            new Response(
                200,
                [
                    'Cache-Control' => 'public, max-age=100',
                    'Content-Length' => 4,
                    'Content-Type' => 'text/plain',
                    'ETag' => '1234567890',
                    'Last-Modified' => 'Wed, 21 Oct 2015 07:28:00 GMT',
                ],
                'test'
            )
        );

        ob_start();
        $client->request('GET', $this->createDownloadUri('http://www.example.com/test.txt'));
        $content = ob_get_clean();

        $response = $client->getResponse();

        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([
            'cache-control' => ['max-age=100, public'],
            'content-length' => ['4'],
            'content-type' => ['text/plain; charset=UTF-8'],
            'etag' => ['1234567890'],
            'last-modified' => ['Wed, 21 Oct 2015 07:28:00 GMT'],
            'content-disposition' => ['attachment'],
        ], $response->headers->all());
        $this->assertSame('test', $content);
    }

    /**
     * @test
     * @dataProvider statusCodeProvider
     */
    public function it_returns_other_status_Codes(int $fileStatusCode, int $expected)
    {
        $client = static::createClient();

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://www.example.com/test.txt'
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
}
