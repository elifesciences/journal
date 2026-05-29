<?php

namespace eLife\Journal\HttpClient;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;

class StaleResponseLoggingHttpClient implements HttpClientInterface
{
    private $logger;
    private $client;

    public function __construct(HttpClientInterface $client, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->client = $client;
    }

    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        $response = $this->client->request($method, $url, $options);

        $cacheInfo = ($response->getHeaders(false)['x-kevinrob-cache'][0] ?? null);

        if ('STALE' !== $cacheInfo) {
            return $response;
        }

        $cacheControlValues = $response->getHeaders(false)['cache-control'] ?? [];
        preg_match('/max-age=(\d+)/', implode(', ', $cacheControlValues), $maxAgeMatch);
        preg_match('/stale-while-revalidate=(\d+)/', implode(', ', $cacheControlValues), $swrMatch);

        $age = (int) ($response->getHeaders(false)['age'][0] ?? 0);
        $maxAge = (int) ($maxAgeMatch[1] ?? 0);
        $maxStaleAge = $maxAge + (int) ($swrMatch[1] ?? 0);

        if ($age > $maxStaleAge) {
            $this->logger->error("Using stale response for {$method} {$url}");
        } elseif ($age > $maxAge) {
            $this->logger->info("Using stale response for {$method} {$url}");
        }

        return $response;
    }

    public function stream($responses, ?float $timeout = null): ResponseStreamInterface
    {
        return $this->client->stream($responses, $timeout);
    }

    public function withOptions(array $options): self
    {
        $clone = clone $this;
        $clone->client = $this->client->withOptions($options);
        return $clone;
    }
}
