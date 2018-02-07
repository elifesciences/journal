<?php

namespace eLife\Journal\Guzzle;

use GuzzleHttp\Promise\PromiseInterface;
use InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use function GuzzleHttp\json_decode;
use function GuzzleHttp\Psr7\normalize_header;
use function GuzzleHttp\Psr7\parse_query;
use function GuzzleHttp\Psr7\stream_for;

final class SubjectRewritingMiddleware
{
    private $replacements = [
        // Dummy rewrite
        'cancer-biology' => [
            'id' => 'immunology',
            'name' => 'Immunology',
        ],
    ];

    public function __invoke(callable $handler)
    {
        return function (RequestInterface $request, array $options) use (&$handler) {
            $uri = $request->getUri();

            foreach ($this->replacements as $replaced => $replacement) {
                switch ($uri->getPath()) {
                    case "/highlights/{$replaced}":
                        $uri = $uri->withPath("/highlights/{$replacement['id']}");
                        break;
                    case  "/subjects/{$replaced}":
                        $uri = $uri->withPath("/subjects/{$replacement['id']}");
                        break;
                }
            }

            $query = parse_query($request->getUri()->getQuery());
            if (!empty($query['subject[]'])) {
                $subjects = array_flip((array) $query['subject[]']);

                foreach ($this->replacements as $replaced => $replacement) {
                    if (isset($subjects[$replaced])) {
                        $uri = $uri->withQuery("{$uri->getQuery()}&subject[]={$replacement['id']}");
                    } elseif (isset($subjects[$replacement['id']])) {
                        $uri = $uri->withQuery("{$uri->getQuery()}&subject[]={$replaced}");
                    }
                }
            }

            $request = $request->withUri($uri);

            /** @var PromiseInterface $promise */
            $promise = $handler($request, $options);

            return $promise->then(function (ResponseInterface $response) use ($request) {
                try {
                    $data = json_decode($response->getBody(), true);
                } catch (InvalidArgumentException $e) {
                    return $response;
                }


                switch (normalize_header($response->getHeaderLine('Content-Type'))[0]) {
                    case 'application/vnd.elife.subject+json;version=1':
                    case 'application/vnd.elife.highlight-list+json;version=1':
                        // Nothing to do.
                        break;
                    case 'application/vnd.elife.subject-list+json;version=1':
                        $before = count($data['items']); // This won't work across pages.

                        $data['items'] = array_filter($data['items'], function (array $subject) {
                            return !in_array($subject['id'], array_keys($this->replacements));
                        });

                        $after = count($data['items']);

                        $data['total'] = $data['total'] - ($before - $after);
                        break;
                    default:
                        $visit = function ($item) use (&$visit) {
                            if (!is_array($item)) {
                                return $item;
                            }

                            if (!empty($item['id']) && !empty($item['name'])) {
                                $keys = array_keys($item);
                                sort($keys);
                                if ($keys === ['id', 'name']) {
                                    if (isset($this->replacements[$item['id']])) {
                                        return $this->replacements[$item['id']];
                                    }
                                }
                            }

                            foreach ($item as $key => $value) {
                                $item[$key] = $visit($value);

                                if (in_array($key, ['expertises', 'subjects'], true)) {
                                    $item[$key] = array_unique($item[$key], SORT_REGULAR);
                                }
                            }

                            return $item;
                        };

                        $data = $visit($data);
                        break;
                }

                return $response->withBody(stream_for(json_encode($data)));
            });
        };
    }
}
