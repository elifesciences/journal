<?php

namespace eLife\Journal\Guzzle;

use GuzzleHttp\Promise\PromiseInterface;
use function GuzzleHttp\Psr7\str;
use Kevinrob\GuzzleCache\CacheMiddleware;
use Kevinrob\GuzzleCache\KeyValueHttpHeader;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

final class StaleLoggingMiddleware
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function __invoke(callable $handler)
    {
        return function (RequestInterface $request, array $options) use (&$handler) {
            /** @var PromiseInterface $promise */
            $promise = $handler($request, $options);

            return $promise->then(function (ResponseInterface $response) use ($request) {
                if (CacheMiddleware::HEADER_CACHE_STALE !== $response->getHeaderLine(CacheMiddleware::HEADER_CACHE_INFO)) {
                    return $response;
                }

                $cacheControl = new KeyValueHttpHeader($response->getHeader('Cache-Control'));

                $age = (int) $response->getHeaderLine('Age');
                $maxAge = (int) $cacheControl->get('max-age');
                $maxStaleAge = $maxAge + ((int) $cacheControl->get('stale-while-revalidate'));

                if ($age > $maxStaleAge) {
                    $this->logger->error("Using stale response for {$request->getMethod()} {$request->getUri()}", ['extra' => ['request' => $this->dumpHttpMessage($request), 'response' => $this->dumpHttpMessage($response)]]);
                } elseif ($age > $maxAge) {
                    $this->logger->info("Using stale response for {$request->getMethod()} {$request->getUri()}", ['extra' => ['request' => $this->dumpHttpMessage($request), 'response' => $this->dumpHttpMessage($response)]]);
                }

                return $response;
            });
        };
    }

    private function dumpHttpMessage(MessageInterface $message)
    {
        return str_replace("\r", '', str($message));
    }
}
