<?php

namespace eLife\Journal\Controller;

use Symfony\Component\HttpFoundation\Response;
use Throwable;
use function GuzzleHttp\Promise\all;

final class StatusController extends Controller
{
    public function pingAction() : Response
    {
        return $this->createResponse('pong', Response::HTTP_OK, ['Content-Type' => 'text/plain']);
    }

    public function statusAction() : Response
    {
        // TODO: Remove $jsErrorSetup before launch

      $jsErrorSetup = <<<'HTML'
<script>
  console.log('This page is used for configuring investigating New Relic JavaScript logging etc.');
  console.log('About to deliberately throw an uncaught ReferenceError.');
  throw new ReferenceError('Deliberately uncaught. Where have I ended up?');
</script>
HTML;

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

        try {
            all($requests)->wait();
        } catch (Throwable $e) {
            return $this->createResponse('<html><head><title>Status</title></head><body>Everything is not ok.'.$jsErrorSetup.' </body></html>', 500);
        }

        return $this->createResponse('<html><head><title>Status</title></head><body>Everything is ok.'.$jsErrorSetup.'</body></html>');
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
