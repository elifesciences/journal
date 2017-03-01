<?php

namespace eLife\Journal\Controller;

use Symfony\Component\HttpFoundation\Response;
use Throwable;
use function GuzzleHttp\Promise\all;
use function GuzzleHttp\Promise\each;


final class StatusController extends Controller
{
    public function pingAction() : Response
    {
        return $this->createResponse('pong', Response::HTTP_OK, ['Content-Type' => 'text/plain']);
    }

    public function statusAction() : Response
    {
        $requests = [
            $this->get('elife.api_sdk.articles')->slice(0, 1),
            $this->get('elife.api_sdk.blog_articles')->slice(0, 1),
            $this->get('elife.api_sdk.collections')->slice(0, 1),
            $this->get('elife.api_sdk.covers')->slice(0, 1),
            $this->get('elife.api_sdk.events')->slice(0, 1),
            $this->get('elife.api_sdk.interviews')->slice(0, 1),
            $this->get('elife.api_sdk.labs_experiments')->slice(0, 1),
            $this->get('elife.api_sdk.medium_articles')->slice(0, 1),
            $this->get('elife.api_sdk.podcast_episodes')->slice(0, 1),
            $this->get('elife.api_sdk.search')->slice(0, 1),
            $this->get('elife.api_sdk.subjects')->slice(0, 1),
        ];

        $requests = each(
            $requests, 
            function() {
                return true;
            },
            function ($reason) {
                $e = exception_for($reason);

                $this->get('logger')->critical('/status failed', ['exception' => $e]);

                return false;
            }
        );

        try {
             $value = $requests->wait();
             var_dump($value);
        } catch (Throwable $e) {
            return $this->createResponse('<html><head><title>Status</title></head><body>Everything is not ok.</body></html>', 500);
        }

        return $this->createResponse('<html><head><title>Status</title></head><body>Everything is ok.</body></html>');
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
