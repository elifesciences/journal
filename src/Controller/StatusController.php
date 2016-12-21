<?php

namespace eLife\Journal\Controller;

use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class StatusController extends Controller
{
    public function pingAction() : Response
    {
        return $this->createResponse('<html><head><title>pong</title></head><body>pong</body></html>');
    }

    public function statusAction() : Response
    {
        try {
            $this->get('elife.api_sdk.search')[0];
        } catch (Throwable $e) {
            return $this->createResponse('<html><head><title>Status</title></head><body>Everything is not ok.</body></html>', 500);
        }

        return $this->createResponse('<html><head><title>Status</title></head><body>Everything is ok.</body></html>');
    }

    private function createResponse(string $body = '', int $statusCode = 200)
    {
        $response = new Response($body, $statusCode);

        $response->headers->set('X-Robots-Tag', 'none');
        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-cache');
        $response->headers->addCacheControlDirective('no-store');
        $response->headers->addCacheControlDirective('must-revalidate');

        return $response;
    }
}
