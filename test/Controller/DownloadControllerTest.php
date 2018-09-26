<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use test\eLife\Journal\WebTestCase;

final class DownloadControllerTest extends WebTestCase
{
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
                fopen($mp3 = __DIR__.'/../../assets/tests/blank.mp3', 'r')
            )
        );

        $content = $this->captureContent(function () use ($client) {
            $client->request('GET', $this->createDownloadUri('http://www.example.com/test.mp3', 'test.mp3'), [], [], ['HTTP_REFERER' => 'http://www.example.com/']);
        });

        $response = $client->getResponse();

        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertArraySubset([
            'content-type' => ['audio/mp3'],
            'content-disposition' => ['attachment; filename="test.mp3"'],
        ], $response->headers->all());
        $this->assertSame(file_get_contents($mp3), $content);
    }

    private function createDownloadUri(string $fileUri, string $name) : string
    {
        $uri = 'http://localhost/download/'.base64_encode($fileUri)."/$name";

        return self::$kernel->getContainer()->get('elife.uri_signer')->sign($uri);
    }

    private function captureContent(callable $callback) : string
    {
        ob_start();

        $callback();

        return ob_get_clean();
    }
}
