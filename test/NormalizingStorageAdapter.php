<?php

namespace test\eLife\Journal;

use Csa\Bundle\GuzzleBundle\Cache\StorageAdapterInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriNormalizer;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use function GuzzleHttp\json_decode;
use function GuzzleHttp\json_encode;

final class NormalizingStorageAdapter implements StorageAdapterInterface
{
    const URI_FLAGS = UriNormalizer::PRESERVING_NORMALIZATIONS | UriNormalizer::SORT_QUERY_PARAMETERS;

    private $storageAdapter;

    public function __construct(StorageAdapterInterface $storageAdapter)
    {
        $this->storageAdapter = $storageAdapter;
    }

    public function fetch(RequestInterface $request)
    {
        return $this->storageAdapter->fetch($this->normalize($request));
    }

    public function save(RequestInterface $request, ResponseInterface $response)
    {
        $this->storageAdapter->save($this->normalize($request), $response);
    }

    private function normalize(RequestInterface $request) : RequestInterface
    {
        $headers = array_change_key_case($request->getHeaders());
        $uri = UriNormalizer::normalize($request->getUri(), self::URI_FLAGS);
        $body = $request->getBody()->__toString();

        if ($body) {
            try {
                if ('application/x-www-form-urlencoded' === $request->getHeaderLine('Content-Type')) {
                    $body = substr(UriNormalizer::normalize(new Uri("'?{$body}"), self::URI_FLAGS)->__toString(), 2);
                } elseif (false !== strpos('json', $request->getHeaderLine('Content-Type'))) {
                    $body = json_encode(json_decode($body));
                }
            } catch (Throwable $e) {
                // Do nothing.
            }
        }

        return new Request(
            $request->getMethod(),
            $uri,
            $headers,
            $body,
            $request->getProtocolVersion()
        );
    }
}
