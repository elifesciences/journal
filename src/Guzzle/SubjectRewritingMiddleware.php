<?php

namespace eLife\Journal\Guzzle;

use eLife\ApiClient\MediaType;
use InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use function GuzzleHttp\json_decode;
use function GuzzleHttp\json_encode;
use function GuzzleHttp\Promise\promise_for;
use function GuzzleHttp\Psr7\parse_query;
use function GuzzleHttp\Psr7\stream_for;

final class SubjectRewritingMiddleware
{
    private $replacements;

    public function __construct(array $replacements = [])
    {
        $this->replacements = array_reduce($replacements, function (array $carry, array $rewrite) {
            return $carry + [$rewrite['from_id'] => [
                    'id' => $rewrite['to_id'],
                    'name' => $rewrite['to_name'],
                ]];
        }, []);
    }

    public function __invoke(callable $handler) : callable
    {
        if (empty($this->replacements)) {
            return $handler;
        }

        return function (RequestInterface $request, array $options = []) use (&$handler) {
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

            return promise_for($handler($request, $options))->then(function (ResponseInterface $response) use ($request) {
                try {
                    $mediaType = MediaType::fromString($response->getHeaderLine('Content-Type'));
                    $data = json_decode($response->getBody(), true);
                } catch (InvalidArgumentException $e) {
                    return $response;
                }

                switch ((string) $mediaType) {
                    case 'application/vnd.elife.subject-list+json; version=1':
                        $before = count($data['items']); // This won't work across pages.

                        $data['items'] = array_values(array_filter($data['items'], function (array $subject) {
                            return !in_array($subject['id'], array_keys($this->replacements));
                        }));

                        $after = count($data['items']);

                        $data['total'] = $data['total'] - ($before - $after);
                        break;

                    case 'application/vnd.elife.article-history+json; version=2':
                        $data['versions'] = $this->updateItems($data['versions']);
                        break;

                    case 'application/vnd.elife.article-list+json; version=1':
                    case 'application/vnd.elife.blog-article-list+json; version=1':
                    case 'application/vnd.elife.collection-list+json; version=1':
                    case 'application/vnd.elife.community-list+json; version=1':
                    case 'application/vnd.elife.press-package-list+json; version=1':
                    case 'application/vnd.elife.recommendations+json; version=1':
                        $data['items'] = $this->updateItems($data['items']);
                        break;

                    case 'application/vnd.elife.article-related+json; version=1':
                        $data = $this->updateItems($data);
                        break;

                    case 'application/vnd.elife.article-poa+json; version=2':
                    case 'application/vnd.elife.article-poa+json; version=3':
                    case 'application/vnd.elife.article--vor+json; version=5':
                    case 'application/vnd.elife.article-vor+json; version=6':
                    case 'application/vnd.elife.blog-article+json; version=2':
                        $data = $this->updateItem($data);
                        break;

                    case 'application/vnd.elife.collection+json; version=1':
                        $data = $this->updateItem($data);
                        $data['content'] = $this->updateItems($data['content']);
                        $data['relatedContent'] = $this->updateItems($data['relatedContent'] ?? []);
                        break;

                    case 'application/vnd.elife.cover-list+json; version=1':
                    case 'application/vnd.elife.highlight-list+json; version=3':
                        $data['items'] = array_map(function (array $cover) {
                            $cover['item'] = $this->updateItem($cover['item']);

                            return $cover;
                        }, $data['items']);
                        break;

                    case 'application/vnd.elife.podcast-episode+json; version=1':
                        $data['chapters'] = array_map(function (array $chapter) {
                            $chapter['content'] = $this->updateItems($chapter['content'] ?? []);

                            return $chapter;
                        }, $data['chapters']);
                        break;

                    case 'application/vnd.elife.person-list+json; version=1':
                        $data['items'] = array_map([$this, 'updatePerson'], $data['items']);
                        break;

                    case 'application/vnd.elife.person+json; version=1':
                        $data = $this->updatePerson($data);
                        break;

                    case 'application/vnd.elife.press-package+json; version=3':
                        $data = $this->updateItem($data);
                        $data['relatedContent'] = $this->updateItems($data['relatedContent'] ?? []);
                        break;

                    case 'application/vnd.elife.search+json; version=1':
                        $data['items'] = $this->updateItems($data['items']);
                        $data['subjects'] = $this->updateSearchSubjects($data['subjects'] ?? []);
                        break;
                }

                return $response->withBody(stream_for(json_encode($data)));
            });
        };
    }

    private function updateItems(array $items) : array
    {
        return array_map([$this, 'updateItem'], $items);
    }

    private function updateItem(array $item) : array
    {
        $item['subjects'] = $this->updateSubjects($item['subjects'] ?? []);

        return $item;
    }

    private function updatePerson(array $person) : array
    {
        if (!isset($person['research'])) {
            return $person;
        }

        $person['research']['expertises'] = $this->updateSubjects($person['research']['expertises']);

        return $person;
    }

    private function updateSearchSubjects(array $subjects) : array
    {
        foreach ($this->replacements as $replaced => $replacement) {
            $total = 0;
            foreach ($subjects as $i => $subject) {
                if ($replaced === $subject['id']) {
                    $total = $subject['results'];
                    unset($subjects[$i]);
                    break;
                }
            }
            if ($total > 0) {
                $subjects = $this->ensureSearchSubject($subjects, $replacement, $total);
            }
        }

        return array_values($subjects);
    }

    private function ensureSearchSubject(array $subjects, array $subject, int $total) : array
    {
        foreach ($subjects as $i => $existingSubject) {
            if ($subject['id'] === $existingSubject['id']) {
                $subjects[$i]['results'] += $total;

                return $subjects;
            }
        }

        $subjects[] = $subject + ['results' => $total];

        return $subjects;
    }

    private function updateSubjects(array $subjects) : array
    {
        if (empty($subjects)) {
            return $subjects;
        }

        foreach ($subjects as $x => $subject) {
            if (isset($this->replacements[$subject['id']])) {
                $subjects[$x] = $this->replacements[$subject['id']];
            }
        }

        return array_values(array_unique($subjects, SORT_REGULAR));
    }
}
