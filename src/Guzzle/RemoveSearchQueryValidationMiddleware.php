<?php

namespace eLife\Journal\Guzzle;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use function GuzzleHttp\Psr7\parse_query;

final class RemoveSearchQueryValidationMiddleware
{
    public function __invoke(callable $handler) : callable
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            return $handler($request, $options)
                ->then(function (ResponseInterface $response) use ($request) : ResponseInterface {
                    if ($this->removeValidation($request)) {
                        $response = $response
                            ->withoutHeader('Etag')
                            ->withoutHeader('Last-Modified');
                    }

                    return $response;
                });
        };
    }

    private function removeValidation(RequestInterface $request) : bool
    {
        if ('/search' !== $request->getUri()->getPath()) {
            return false;
        }

        $parameters = parse_query($request->getUri()->getQuery());

        return !empty($parameters['for']);
    }
}
