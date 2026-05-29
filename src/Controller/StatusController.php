<?php

namespace eLife\Journal\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

final class StatusController extends Controller
{
    const STATUS_OK = 'ok';
    const STATUS_FAILING = 'failing';
    /**
     * @var HttpClientInterface
     */
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function pingAction() : Response
    {
        $this->get('cache.app')->getItem('ping')->get();

        return $this->createResponse('pong', Response::HTTP_OK, ['Content-Type' => 'text/plain']);
    }

    public function statusAction() : Response
    {
        $responses = array_map(function (string $uri) {
            return $this->client->request('GET', $uri, ['buffer' => false, 'headers' => ['Cache-Control' => 'no-cache, no-store']]);
        }, $this->getParameter('status_checks'));

        ksort($responses);

        $status = Response::HTTP_OK;
        $checks = [];

        foreach ($responses as $name => $response) {
            try {
                $statusCode = $response->getStatusCode();
                if ($statusCode >= 400) {
                    throw new \RuntimeException("Unexpected status $statusCode");
                }
                $checks[] = ['name' => $name, 'status' => self::STATUS_OK, 'message' => null];
            } catch (Throwable $e) {
                $this->get('elife.logger')->critical("$name status check failed", ['exception' => $e]);
                $checks[] = ['name' => $name, 'status' => self::STATUS_FAILING, 'message' => $e->getMessage()];
                $status = Response::HTTP_INTERNAL_SERVER_ERROR;
            }
        }

        return $this->createResponse($this->get('templating')->render('::status.html.twig', ['checks' => $checks]), $status);
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
