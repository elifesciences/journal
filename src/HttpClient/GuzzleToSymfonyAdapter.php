<?php

namespace eLife\Journal\HttpClient;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class GuzzleToSymfonyAdapter implements ClientInterface
{
    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function send(RequestInterface $request, array $options = [])
    {
        $body = $request->getBody();
        $body->rewind();
        $bodyStr = (string) $body;

        $symfonyResponse = $this->httpClient->request(
            $request->getMethod(),
            (string) $request->getUri(),
            [
                'headers' => array_map(function ($values) { return implode(', ', $values); }, $request->getHeaders()),
                'body' => $bodyStr ?: null,
            ]
        );

        $statusCode = $symfonyResponse->getStatusCode();
        $headers = $symfonyResponse->getHeaders(false);
        $content = $symfonyResponse->getContent(false);

        return new Response($statusCode, $headers, $content);
    }

    public function sendAsync(RequestInterface $request, array $options = []): PromiseInterface
    {
        return new FulfilledPromise($this->send($request, $options));
    }

    public function request($method, $uri, array $options = [])
    {
        $headers = $options['headers'] ?? [];
        $body = null;
        if (isset($options['form_params'])) {
            $body = http_build_query($options['form_params']);
            $headers['Content-Type'] = 'application/x-www-form-urlencoded';
        } elseif (isset($options['body'])) {
            $body = $options['body'];
        }

        $symfonyResponse = $this->httpClient->request($method, (string) $uri, [
            'headers' => $headers,
            'body' => $body,
        ]);

        $statusCode = $symfonyResponse->getStatusCode();
        $responseHeaders = $symfonyResponse->getHeaders(false);
        $content = $symfonyResponse->getContent(false);

        return new Response($statusCode, $responseHeaders, $content);
    }

    public function requestAsync($method, $uri, array $options = []): PromiseInterface
    {
        return new FulfilledPromise($this->request($method, $uri, $options));
    }

    public function getConfig($option = null)
    {
        return null;
    }
}
