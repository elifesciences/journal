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

      $jsErrorSetup = <<<'HTML'
<script>
  console.log('This page is used for configuring investigating New Relic JavaScript logging etc.');
  console.log('About to deliberately throw an uncaught ReferenceError.');
  throw new ReferenceError('Deliberately uncaught. Where have I ended up?');
</script>
HTML;

        try {
            $this->get('elife.api_sdk.articles')[0];
            $this->get('elife.api_sdk.blog_articles')[0];
            $this->get('elife.api_sdk.collections')[0];
            $this->get('elife.api_sdk.covers')[0];
            $this->get('elife.api_sdk.events')[0];
            $this->get('elife.api_sdk.interviews')[0];
            $this->get('elife.api_sdk.labs_experiments')[0];
            $this->get('elife.api_sdk.medium_articles')[0];
            $this->get('elife.api_sdk.podcast_episodes')[0];
            $this->get('elife.api_sdk.search')[0];
            $this->get('elife.api_sdk.subjects')[0];
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
