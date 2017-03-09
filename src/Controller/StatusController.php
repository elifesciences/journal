<?php

namespace eLife\Journal\Controller;

use eLife\ApiClient\Exception\BadResponse;
use eLife\ApiSdk\Model\ArticleVersion;
use eLife\Journal\Helper\Callback;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use function GuzzleHttp\Promise\all;
use function GuzzleHttp\Promise\exception_for;
use function GuzzleHttp\Promise\rejection_for;

final class StatusController extends Controller
{
    const STATUS_OK = 'ok';
    const STATUS_FAILING = 'failing';
    const STATUS_UNKNOWN = 'unknown';

    public function pingAction() : Response
    {
        return $this->createResponse('pong', Response::HTTP_OK, ['Content-Type' => 'text/plain']);
    }

    public function statusAction() : Response
    {
        $requests = [
            'Articles' => $article = $this->get('elife.api_sdk.articles')->reverse()->slice(0, 1),
            'Inside eLife' => $this->get('elife.api_sdk.blog_articles')->slice(0, 1),
            'Collections' => $this->get('elife.api_sdk.collections')->slice(0, 1),
            'Covers' => $this->get('elife.api_sdk.covers')->slice(0, 1),
            'Events' => $this->get('elife.api_sdk.events')->slice(0, 1),
            'Interviews' => $this->get('elife.api_sdk.interviews')->slice(0, 1),
            'Labs' => $this->get('elife.api_sdk.labs_experiments')->slice(0, 1),
            'Medium' => $this->get('elife.api_sdk.medium_articles')->slice(0, 1),
            'Metrics' => $article->then(Callback::method('offsetGet', 0))
                ->then(Callback::emptyOr(function (ArticleVersion $article) {
                    return $this->get('elife.api_sdk.metrics')->totalPageViews('article', $article->getId());
                }), function () {
                    return null;
                })
                ->otherwise(function ($reason) {
                    if ($reason instanceof BadResponse && in_array($reason->getResponse()->getStatusCode(), [404, 410])) {
                        return null;
                    }

                    return rejection_for($reason);
                }),
            'Podcast' => $this->get('elife.api_sdk.podcast_episodes')->slice(0, 1),
            'Recommendations' => $article->then(Callback::method('offsetGet', 0))
                ->then(Callback::emptyOr(function (ArticleVersion $article) {
                    return $this->get('elife.api_sdk.recommendations')->list('article', $article->getId())->slice(0, 1);
                }), function () {
                    return null;
                })
                ->otherwise(function ($reason) {
                    if ($reason instanceof BadResponse && in_array($reason->getResponse()->getStatusCode(), [404, 410])) {
                        return null;
                    }

                    return rejection_for($reason);
                }),
            'Search' => $this->get('elife.api_sdk.search')->slice(0, 1),
            'Subjects' => $this->get('elife.api_sdk.subjects')->slice(0, 1),
        ];

        try {
            all($requests)->wait();
            $status = Response::HTTP_OK;
        } catch (Throwable $e) {
            $status = Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        ksort($requests);

        foreach ($requests as $name => $request) {
            $requests[$name] = $request
                ->then(function ($check) use ($name) {
                    if (null === $check) {
                        $this->get('logger')->warning("$name status check not possible");

                        return [
                            'name' => $name,
                            'status' => self::STATUS_UNKNOWN,
                            'message' => 'Unknown',
                        ];
                    }

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
