<?php

namespace eLife\Journal\Guzzle;

use eLife\ApiSdk\ApiClient\ArticlesClient;
use eLife\ApiSdk\ApiClient\CollectionsClient;
use eLife\ApiSdk\ApiClient\CommunityClient;
use eLife\ApiSdk\ApiClient\CoversClient;
use eLife\ApiSdk\ApiClient\HighlightsClient;
use eLife\ApiSdk\ApiClient\PodcastClient;
use eLife\ApiSdk\ApiClient\PressPackagesClient;
use eLife\ApiSdk\ApiClient\RecommendationsClient;
use eLife\ApiSdk\ApiClient\SearchClient;
use eLife\ApiClient\MediaType;
use eLife\ApiSdk\Client\Articles;
use eLife\ApiSdk\Client\Collections;
use eLife\ApiSdk\Client\Community;
use eLife\ApiSdk\Client\Covers;
use eLife\ApiSdk\Client\Highlights;
use eLife\ApiSdk\Client\PodcastEpisodes;
use eLife\ApiSdk\Client\PressPackages;
use eLife\ApiSdk\Client\Recommendations;
use eLife\ApiSdk\Client\Search;
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
                    case (string) new MediaType(ArticlesClient::TYPE_ARTICLE_HISTORY, Articles::VERSION_ARTICLE_HISTORY):
                        $data['versions'] = $this->updateItems($data['versions']);
                        break;

                    case (string) new MediaType(ArticlesClient::TYPE_ARTICLE_LIST, Articles::VERSION_ARTICLE_LIST):
                    case (string) new MediaType(CommunityClient::TYPE_COMMUNITY_LIST, Community::VERSION_COMMUNITY_LIST):
                    case (string) new MediaType(RecommendationsClient::TYPE_RECOMMENDATIONS, Recommendations::VERSION_RECOMMENDATIONS):
                        $data['items'] = $this->updateItems($data['items']);
                        break;

                    case (string) new MediaType(ArticlesClient::TYPE_ARTICLE_RELATED, Articles::VERSION_ARTICLE_RELATED):
                        $data = $this->updateItems($data);
                        break;

                    case (string) new MediaType(ArticlesClient::TYPE_ARTICLE_POA, Articles::VERSION_ARTICLE_POA):
                    case (string) new MediaType(ArticlesClient::TYPE_ARTICLE_VOR, Articles::VERSION_ARTICLE_VOR):
                        $data = $this->updateItem($data);
                        break;

                    case (string) new MediaType(CollectionsClient::TYPE_COLLECTION, Collections::VERSION_COLLECTION):
                        $data['content'] = $this->updateItems($data['content']);
                        $data['relatedContent'] = $this->updateItems($data['relatedContent'] ?? []);
                        break;

                    case (string) new MediaType(CoversClient::TYPE_COVERS_LIST, Covers::VERSION_COVERS_LIST):
                    case (string) new MediaType(HighlightsClient::TYPE_HIGHLIGHT_LIST, Highlights::VERSION_HIGHLIGHT_LIST):
                        $data['items'] = array_map(function (array $cover) {
                            $cover['item'] = $this->updateItem($cover['item']);

                            return $cover;
                        }, $data['items']);
                        break;

                    case (string) new MediaType(PodcastClient::TYPE_PODCAST_EPISODE, PodcastEpisodes::VERSION_PODCAST_EPISODE):
                        $data['chapters'] = array_map(function (array $chapter) {
                            $chapter['content'] = $this->updateItems($chapter['content'] ?? []);

                            return $chapter;
                        }, $data['chapters']);
                        break;

                    case (string) new MediaType(PressPackagesClient::TYPE_PRESS_PACKAGE, PressPackages::VERSION_PRESS_PACKAGE):
                        $data['relatedContent'] = $this->updateItems($data['relatedContent'] ?? []);
                        break;

                    case (string) new MediaType(SearchClient::TYPE_SEARCH, Search::VERSION_SEARCH):
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
