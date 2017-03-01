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
        $requests = [
            'articles' => $this->get('elife.api_sdk.articles')->slice(0, 1),
            'blog-articles' => $this->get('elife.api_sdk.blog_articles')->slice(0, 1),
            'collections' => $this->get('elife.api_sdk.collections')->slice(0, 1),
            'covers' => $this->get('elife.api_sdk.covers')->slice(0, 1),
            'events' => $this->get('elife.api_sdk.events')->slice(0, 1),
            'interviews' => $this->get('elife.api_sdk.interviews')->slice(0, 1),
            'labs-experiments' => $this->get('elife.api_sdk.labs_experiments')->slice(0, 1),
            'medium-articles' => $this->get('elife.api_sdk.medium_articles')->slice(0, 1),
            'podcast-episodes' => $this->get('elife.api_sdk.podcast_episodes')->slice(0, 1),
            'search' => $this->get('elife.api_sdk.search')->slice(0, 1),
            'subjects' => $this->get('elife.api_sdk.subjects')->slice(0, 1),
        ];

        $responsePromises = array_map(function ($request) {
            return $request
                ->then(function ($response) {
                    return $response->status();
                })
                ->otherwise(function ($reason) {
                    return $reason;
                });
        }, $requests);

        $responses = all($responsePromises)->wait();
        $problems = array_filter($responses, function ($response) {
            return $response instanceof Throwable;
        });

        if ($problems) {
            $status = 500;
            $text = 'Everything is not ok';
        } else {
            $status = 200;
            $text = 'Everything is ok';
        }
        $items = array_map(function ($response) {
            if ($response instanceof Throwable) {
                $this->get('logger')->critical('/status failed', ['exception' => $response]);

                return $response->getMessage();
            }

            return $response->getStatusCode();
        }, $responses);
        $list = '<ul>'.PHP_EOL;
        foreach ($items as $api => $item) {
            $list .= '<li>'.$api.': '.$item.'</li>'.PHP_EOL;
        }
        $list .= '</ul>'.PHP_EOL;

        return $this->createResponse(
            '<html>'.PHP_EOL
            .'<head>'.PHP_EOL
            .'<title>Status</title>'.PHP_EOL
            .'</head>'.PHP_EOL
            .'<body>'.PHP_EOL
            .$text.$list.PHP_EOL
            .'</body>'.PHP_EOL
            .'</html>',
            $status
        );
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
