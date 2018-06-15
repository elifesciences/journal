<?php

namespace eLife\Journal\Helper;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\TransferException;
use Psr\Http\Message\ResponseInterface;
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
        try {
            $backendResponse = $this->sendRequest($request, $uri);
        } catch (TransferException $e) {
            if ($e instanceof BadResponseException) {
                $backendResponse = $e->getResponse();

                switch ($backendResponse->getStatusCode()) {
                    case Response::HTTP_NOT_MODIFIED:
                        $response = new Response('', $backendResponse->getStatusCode());
                        break;
                    case Response::HTTP_NOT_FOUND:
                    case Response::HTTP_GONE:
                        throw new HttpException($backendResponse->getStatusCode(), $e->getMessage(), $e);
                }

                if (isset($response)) {
                    return $this->finishResponse($response, $backendResponse);
                }
            }

            throw new HttpException(Response::HTTP_BAD_GATEWAY, $e->getMessage(), $e);
        }

        $response = $this->createResponse($backendResponse);

        return $this->finishResponse($response, $backendResponse);
    }

    private function sendRequest(Request $request, string $uri) : ResponseInterface
    {
        $xForwardedFor = array_filter(array_map('trim', explode(',', $request->isFromTrustedProxy() ? $request->headers->get('X-Forwarded-For') : '')));
        $xForwardedFor[] = $request->server->get('REMOTE_ADDR');

        return $this->client->request('GET', $uri, [
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
            'http_errors' => true,
        ]);
    }

    private function createResponse(ResponseInterface $backendResponse) : Response
    {
        $stream = $backendResponse->getBody();

        return new StreamedResponse(
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
    }

    private function finishResponse(Response $response, ResponseInterface $backendResponse) : Response
    {
        $response->headers->remove('Cache-Control');

        $response->headers->add(array_filter(['Cache-Control' => $backendResponse->getHeaderLine('Cache-Control'),
            'Content-Length' => $backendResponse->getHeaderLine('Content-Length'),
            'Content-Type' => $backendResponse->getHeaderLine('Content-Type'),
            'Date' => $backendResponse->getHeaderLine('Date'),
            'ETag' => $backendResponse->getHeaderLine('ETag'),
            'Expires' => $backendResponse->getHeaderLine('Expires'),
            'Last-Modified' => $backendResponse->getHeaderLine('Last-Modified'),
            'Vary' => $backendResponse->getHeaderLine('Vary'), ]));

        return $response;
    }
}
