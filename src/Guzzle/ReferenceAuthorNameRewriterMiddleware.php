<?php

namespace eLife\Journal\Guzzle;

use eLife\ApiClient\MediaType;
use InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use function GuzzleHttp\json_decode;
use function GuzzleHttp\json_encode;
use function GuzzleHttp\Promise\promise_for;
use function GuzzleHttp\Psr7\stream_for;

final class ReferenceAuthorNameRewriterMiddleware
{
    public function __invoke(callable $handler) : callable
    {
        return function (RequestInterface $request, array $options = []) use (&$handler) {
            return promise_for($handler($request, $options))->then(function (ResponseInterface $response) use ($request) {
                try {
                    $mediaType = MediaType::fromString($response->getHeaderLine('Content-Type'));
                    $data = json_decode($response->getBody(), true);
                } catch (InvalidArgumentException $e) {
                    return $response;
                }

                if ('application/vnd.elife.article-vor+json' === $mediaType->getType()) {
                    $data = $this->updateItem($data);
                }

                return $response->withBody(stream_for(json_encode($data)));
            });
        };
    }

    private function updateItem(array $item) : array
    {
        if (!empty($item['references'])) {
            $item['references'] = array_map(function ($reference) {
                if (!empty($reference['authors'])) {
                    $reference['authors'] = array_map(function ($author) {
                        if ('person' === $author['type']) {
                            $author['name']['preferred'] = str_replace(',', '', $author['name']['index']);
                        }
                        return $author;
                    }, $reference['authors']);
                }
                return $reference;
            }, $item['references']);
        }

        return $item;
    }
}
