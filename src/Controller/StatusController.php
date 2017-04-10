<?php

namespace eLife\Journal\Controller;

use Symfony\Component\HttpFoundation\Response;
use Throwable;
use function GuzzleHttp\Promise\all;
use function GuzzleHttp\Promise\exception_for;

final class StatusController extends Controller
{
    const STATUS_OK = 'ok';
    const STATUS_FAILING = 'failing';

    public function pingAction() : Response
    {
        return $this->createResponse('pong', Response::HTTP_OK, ['Content-Type' => 'text/plain']);
    }

    public function statusAction() : Response
    {
        $requests = array_map(function (string $uri) {
            return $this->get('csa_guzzle.client.elife_api')->requestAsync('GET', $uri, ['headers' => ['Cache-Control' => 'no-cache, no-store']]);
        }, $this->getParameter('status_checks'));

        try {
            all($requests)->wait();
            $status = Response::HTTP_OK;
        } catch (Throwable $e) {
            $status = Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        ksort($requests);

        foreach ($requests as $name => $request) {
            $requests[$name] = $request
                ->then(function () use ($name) {
                    return [
                        'name' => $name,
                        'status' => self::STATUS_OK,
                        'message' => null,
                    ];
                })
                ->otherwise(function ($reason) use ($name) {
                    $exception = exception_for($reason);
                    $this->get('logger')->critical("$name status check failed", compact('exception'));

                    return [
                        'name' => $name,
                        'status' => self::STATUS_FAILING,
                        'message' => $exception->getMessage(),
                    ];
                });
        }

        return $this->createResponse($this->get('templating')->render('::status.html.twig', ['checks' => all($requests)->wait()]), $status);
    }

    private function createResponse(string $body = '', int $statusCode = Response::HTTP_OK, array $headers = [])
    {
        $response = new Response($body, $statusCode, $headers);

        $response->headers->set('X-Robots-Tag', 'none');
        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-cache');
        $response->headers->addCacheControlDirective('no-store');
        $response->headers->addCacheControlDirective('must-revalidate');

        return $response;
    }
}
