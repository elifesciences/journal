<?php

namespace eLife\Journal\Helper;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class FileStreamer
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function getResponse(Request $request, string $uri) : Response
    {
        $xForwardedFor = array_filter(array_map('trim', explode(',', $request->isFromTrustedProxy() ? $request->headers->get('X-Forwarded-For') : '')));
        $xForwardedFor[] = $request->server->get('REMOTE_ADDR');

        /** @var ResponseInterface $fileResponse */
        $fileResponse = $this->client->request('GET', $uri, [
            'headers' => array_filter([
                'Accept' => $request->headers->get('Accept'),
                'Cache-Control' => $request->headers->get('Cache-Control'),
                'If-Modified-Since' => $request->headers->get('If-Modified-Since'),
                'If-None-Match' => $request->headers->get('If-None-Match'),
                'Referer' => $request->headers->get('Referer'),
                'X-Forwarded-For' => implode(', ', $xForwardedFor),
                'X-Forwarded-Host' => $request->getHost(),
                'X-Forwarded-Port' => $request->getPort(),
                'X-Forwarded-Proto' => $request->getScheme(),
            ]),
        ]);

        $stream = $fileResponse->getBody();

        switch ($fileResponse->getStatusCode()) {
            case Response::HTTP_OK:
                $response = new StreamedResponse(
                    function () use ($stream) {
                        if (ob_get_length()) {
                            ob_end_clean();
                        }
                        while (!$stream->eof()) {
                            echo $stream->read(1024);
                            flush();
                        }
                        $stream->close();
                    },
                    $fileResponse->getStatusCode()
                );
                break;
            case Response::HTTP_NOT_MODIFIED:
                $response = new Response('', $fileResponse->getStatusCode());
                break;
            case Response::HTTP_NOT_FOUND:
            case Response::HTTP_GONE:
                throw new HttpException($fileResponse->getStatusCode(), $fileResponse->getReasonPhrase());
            default:
                throw new RuntimeException("Failed: {$fileResponse->getStatusCode()}, {$fileResponse->getReasonPhrase()}");
        }

        $response->headers->remove('Cache-Control');

        $response->headers->add(array_filter([
            'Cache-Control' => $fileResponse->getHeaderLine('Cache-Control'),
            'Content-Length' => $fileResponse->getHeaderLine('Content-Length'),
            'Content-Type' => $fileResponse->getHeaderLine('Content-Type'),
            'Date' => $fileResponse->getHeaderLine('Date'),
            'ETag' => $fileResponse->getHeaderLine('ETag'),
            'Expires' => $fileResponse->getHeaderLine('Expires'),
            'Last-Modified' => $fileResponse->getHeaderLine('Last-Modified'),
            'Vary' => $fileResponse->getHeaderLine('Vary'),
        ]));

        return $response;
    }
}
