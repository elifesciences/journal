<?php

namespace eLife\Journal\Helper;

use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class HttpProxy
{
    private $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function send(Request $request, string $uri) : Response
    {
        $xForwardedFor = array_filter(array_map('trim', explode(',', $request->isFromTrustedProxy() ? $request->headers->get('X-Forwarded-For') : '')));
        $xForwardedFor[] = $request->server->get('REMOTE_ADDR');

        /** @var ResponseInterface $backendResponse */
        $backendResponse = $this->client->request('GET', $uri, [
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

        switch ($backendResponse->getStatusCode()) {
            case Response::HTTP_OK:
                $stream = $backendResponse->getBody();

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
                    $backendResponse->getStatusCode()
                );
                break;
            case Response::HTTP_NOT_MODIFIED:
                $response = new Response('', $backendResponse->getStatusCode());
                break;
            case Response::HTTP_NOT_FOUND:
            case Response::HTTP_GONE:
                throw new HttpException($backendResponse->getStatusCode(), $backendResponse->getReasonPhrase());
            default:
                throw new RuntimeException("Failed: {$backendResponse->getStatusCode()}, {$backendResponse->getReasonPhrase()}");
        }

        $response->headers->remove('Cache-Control');

        $response->headers->add(array_filter([
            'Cache-Control' => $backendResponse->getHeaderLine('Cache-Control'),
            'Content-Length' => $backendResponse->getHeaderLine('Content-Length'),
            'Content-Type' => $backendResponse->getHeaderLine('Content-Type'),
            'Date' => $backendResponse->getHeaderLine('Date'),
            'ETag' => $backendResponse->getHeaderLine('ETag'),
            'Expires' => $backendResponse->getHeaderLine('Expires'),
            'Last-Modified' => $backendResponse->getHeaderLine('Last-Modified'),
            'Vary' => $backendResponse->getHeaderLine('Vary'),
        ]));

        return $response;
    }
}
