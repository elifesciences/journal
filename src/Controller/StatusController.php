<?php

namespace eLife\Journal\Controller;

use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class StatusController extends Controller
{
    public function pingAction() : Response
    {
        return $this->createResponse('pong', Response::HTTP_OK, ['Content-Type' => 'text/plain']);
    }

    public function statusAction() : Response
    {
      // TODO: Remove $jsErrorSetup before launch
      $jsErrorSetup = '<script>';
      $jsErrorSetup .= 'console.log(\'This page is used for configuring investigating New Relic JavaScript logging etc.\');' . "\n";
      $jsErrorSetup .= 'console.log(\'About to deliberately throw an uncaught ReferenceError.\');' . "\n";
      $jsErrorSetup .= 'throw new ReferenceError(\'Deliberately uncaught. Where have I ended up?\');' . "\n";
      $jsErrorSetup .= '</script>';

        try {
            $this->get('elife.api_sdk.search')[0];
        } catch (Throwable $e) {
            return $this->createResponse('<html><head><title>Status</title></head><body>Everything is not ok.' . $jsErrorSetup . ' </body></html>', 500);
        }

        return $this->createResponse('<html><head><title>Status</title></head><body>Everything is ok.' . $jsErrorSetup . '</body></html>');
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
