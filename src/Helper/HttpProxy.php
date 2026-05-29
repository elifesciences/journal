<?php

namespace eLife\Journal\Helper;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class HttpProxy
{
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function send(Request $request, string $uri) : Response
    {
        try {
            $backendResponse = $this->sendRequest($request, $uri);
            $statusCode = $backendResponse->getStatusCode();
        } catch (TransportExceptionInterface $e) {
            throw new HttpException(Response::HTTP_BAD_GATEWAY, $e->getMessage(), $e);
        }

        switch ($statusCode) {
            case Response::HTTP_NOT_MODIFIED:
                $response = new Response('', $statusCode);
                return $this->finishResponse($response, $backendResponse);
            case Response::HTTP_NOT_FOUND:
            case Response::HTTP_GONE:
                throw new HttpException($statusCode);
        }

        if ($statusCode >= 500) {
            throw new HttpException(Response::HTTP_BAD_GATEWAY);
        }

        return $this->finishResponse($this->createResponse($backendResponse), $backendResponse);
    }

    private function sendRequest(Request $request, string $uri) : ResponseInterface
    {
        $xForwardedFor = array_filter(array_map('trim', explode(',', $request->isFromTrustedProxy() ? $request->headers->get('X-Forwarded-For') : '')));
        $xForwardedFor[] = $request->server->get('REMOTE_ADDR');

        return $this->client->request('GET', $uri, [
            'buffer' => false,
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
    }

    private function createResponse(ResponseInterface $backendResponse) : Response
    {
        $client = $this->client;

        return new StreamedResponse(
            function () use ($backendResponse, $client) {
                if (ob_get_length()) {
                    ob_end_clean();
                }

                foreach ($client->stream($backendResponse) as $chunk) {
                    echo $chunk->getContent();
                    flush();
                }
            },
            $backendResponse->getStatusCode()
        );
    }

    private function finishResponse(Response $response, ResponseInterface $backendResponse) : Response
    {
        $headers = $backendResponse->getHeaders(false);

        $response->headers->remove('Cache-Control');

        $response->headers->add(array_filter([
            'Cache-Control' => $headers['cache-control'][0] ?? null,
            'Content-Length' => $headers['content-length'][0] ?? null,
            'Content-Type' => $headers['content-type'][0] ?? null,
            'Date' => $headers['date'][0] ?? null,
            'ETag' => $headers['etag'][0] ?? null,
            'Expires' => $headers['expires'][0] ?? null,
            'Last-Modified' => $headers['last-modified'][0] ?? null,
            'Vary' => $headers['vary'][0] ?? null,
        ]));

        return $response;
    }
}
