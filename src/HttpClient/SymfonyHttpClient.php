<?php

namespace eLife\Journal\HttpClient;

use Crell\ApiProblem\ApiProblem;
use Crell\ApiProblem\JsonParseException;
use eLife\ApiClient\Exception\ApiException;
use eLife\ApiClient\Exception\ApiProblemResponse;
use eLife\ApiClient\Exception\ApiTimeout;
use eLife\ApiClient\Exception\BadResponse;
use eLife\ApiClient\Exception\NetworkProblem;
use eLife\ApiClient\HttpClient;
use eLife\ApiClient\Result\HttpResult;
use GuzzleHttp\Promise\Create;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class SymfonyHttpClient implements HttpClient
{
    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * Issues the request without blocking, then wraps the still-pending Symfony response in
     * a lazily-resolved promise. Reading the response is deferred to the promise's wait
     * function, so requests started before ->wait() is called on any of them are multiplexed
     * concurrently by the underlying Symfony HttpClient instead of being awaited one by one.
     */
    public function send(RequestInterface $request): PromiseInterface
    {
        $body = $request->getBody();
        $body->rewind();

        try {
            $symfonyResponse = $this->httpClient->request(
                $request->getMethod(),
                (string) $request->getUri(),
                [
                    'headers' => array_map(function ($values) { return implode(', ', $values); }, $request->getHeaders()),
                    'body' => (string) $body ?: null,
                ]
            );
        } catch (TransportExceptionInterface $e) {
            if (stripos($e->getMessage(), 'timeout') !== false) {
                return Create::rejectionFor(new ApiTimeout($e->getMessage(), $request, $e));
            }

            return Create::rejectionFor(new NetworkProblem($e->getMessage(), $request, $e));
        } catch (\Throwable $e) {
            return Create::rejectionFor(new ApiException($e->getMessage(), $e));
        }

        $guard = new UnreadResponseGuard($symfonyResponse);

        $promise = new Promise(function () use (&$promise, $request, $guard) {
            $symfonyResponse = $guard->read();

            try {
                $statusCode = $symfonyResponse->getStatusCode();
                $headers = $symfonyResponse->getHeaders(false);
                $content = $symfonyResponse->getContent(false);
            } catch (TransportExceptionInterface $e) {
                if (stripos($e->getMessage(), 'timeout') !== false) {
                    $promise->reject(new ApiTimeout($e->getMessage(), $request, $e));
                } else {
                    $promise->reject(new NetworkProblem($e->getMessage(), $request, $e));
                }

                return;
            } catch (\Throwable $e) {
                $promise->reject(new ApiException($e->getMessage(), $e));

                return;
            }

            $psr7Response = new Response($statusCode, $headers, $content);

            if ($statusCode >= 400) {
                if ('application/problem+json' === $psr7Response->getHeaderLine('Content-Type')) {
                    try {
                        $promise->reject(new ApiProblemResponse(
                            ApiProblem::fromJson($content),
                            $request,
                            $psr7Response
                        ));

                        return;
                    } catch (JsonParseException $e) {
                        // fall through to BadResponse
                    }
                }

                $promise->reject(new BadResponse(
                    'Unexpected response status '.$statusCode,
                    $request,
                    $psr7Response
                ));

                return;
            }

            try {
                $promise->resolve(HttpResult::fromResponse($psr7Response));
            } catch (\Throwable $e) {
                $promise->reject(new ApiException($e->getMessage(), $e));
            }
        });

        return $promise;
    }
}