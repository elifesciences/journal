<?php

namespace eLife\Journal\Guzzle;

use eLife\ApiClient\MediaType;
use InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use function GuzzleHttp\json_decode;
use function GuzzleHttp\json_encode;
use function GuzzleHttp\Promise\promise_for;
use function GuzzleHttp\Psr7\stream_for;

final class StatusDateOverrideMiddleware
{
    private $authorizationChecker;
    private $eraArticles;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker, array $eraArticles = [])
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->eraArticles = $eraArticles;
    }

    public function __invoke(callable $handler) : callable
    {
        if (empty($this->eraArticles)) {
            return $handler;
        }

        return function (RequestInterface $request, array $options = []) use (&$handler) {
            return promise_for($handler($request, $options))->then(function (ResponseInterface $response) use ($request) {
                try {
                    $mediaType = MediaType::fromString($response->getHeaderLine('Content-Type'));
                    $data = json_decode($response->getBody(), true);
                } catch (InvalidArgumentException $e) {
                    return $response;
                }

                switch ((string) $mediaType) {
                    case 'application/vnd.elife.article-history+json; version=1':
                        $data['versions'] = $this->updateItems($data['versions']);
                        break;

                    case 'application/vnd.elife.article-list+json; version=1':
                    case 'application/vnd.elife.community-list+json; version=1':
                    case 'application/vnd.elife.recommendations+json; version=1':
                        $data['items'] = $this->updateItems($data['items']);
                        break;

                    case 'application/vnd.elife.article-related+json; version=1':
                        $data = $this->updateItems($data);
                        break;

                    case 'application/vnd.elife.article-poa+json; version=2':
                    case 'application/vnd.elife.article-poa+json; version=3':
                    case 'application/vnd.elife.article-vor+json; version=4':
                    case 'application/vnd.elife.article-vor+json; version=5':
                        $data = $this->updateItem($data);
                        break;

                    case 'application/vnd.elife.collection+json; version=1':
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

                    case 'application/vnd.elife.press-package+json; version=3':
                        $data['relatedContent'] = $this->updateItems($data['relatedContent'] ?? []);
                        break;

                    case 'application/vnd.elife.search+json; version=1':
                        $data['items'] = $this->updateItems($data['items']);
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
        if (isset($item['statusDate']) && isset($this->eraArticles[$item['id']])) {
            $item['statusDate'] = $this->eraArticles[$item['id']]['date'];
        }

        return $item;
    }
}
