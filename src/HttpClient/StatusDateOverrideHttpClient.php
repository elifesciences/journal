<?php

namespace eLife\Journal\HttpClient;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;

class StatusDateOverrideHttpClient implements HttpClientInterface
{
    private $eraArticles;
    /**
     * @var HttpClientInterface
     */
    private $client;

    public function __construct(HttpClientInterface $client, array $eraArticles = [])
    {
        $this->eraArticles = $eraArticles;
        $this->client = $client;
    }

    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        $response = $this->client->request($method, $url, $options);

        if (empty($this->eraArticles)) {
            return $response;
        }

        return new StatusDateOverrideResponse($response, $this->eraArticles);
    }

    public function stream($responses, ?float $timeout = null): ResponseStreamInterface
    {
        return $this->client->stream($responses, $timeout);
    }

    public function withOptions(array $options): StatusDateOverrideHttpClient
    {
        $clone = clone $this;
        $clone->client = $this->client->withOptions($options);
        return $clone;
    }
}