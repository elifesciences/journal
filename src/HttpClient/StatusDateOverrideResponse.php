<?php

namespace eLife\Journal\HttpClient;

use eLife\ApiClient\MediaType;
use eLife\ApiSdk\ApiClient\ArticlesClient;
use eLife\ApiSdk\ApiClient\CollectionsClient;
use eLife\ApiSdk\ApiClient\CommunityClient;
use eLife\ApiSdk\ApiClient\CoversClient;
use eLife\ApiSdk\ApiClient\HighlightsClient;
use eLife\ApiSdk\ApiClient\PodcastClient;
use eLife\ApiSdk\ApiClient\PressPackagesClient;
use eLife\ApiSdk\ApiClient\RecommendationsClient;
use eLife\ApiSdk\ApiClient\SearchClient;
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
use Symfony\Contracts\HttpClient\ResponseInterface;

class StatusDateOverrideResponse implements ResponseInterface
{
    private $response;
    private $eraArticles;

    public function __construct(ResponseInterface $response, array $eraArticles)
    {
        $this->response = $response;
        $this->eraArticles = $eraArticles;
    }

    public function getHeaders(bool $throw = true): array
    {
        return $this->response->getHeaders($throw);
    }

    public function getStatusCode(): int
    {
       return $this->response->getStatusCode();
    }
    public function getContent(bool $throw = true): string
    {
        $content = $this->response->getContent($throw);
        try {
            $contentType = $this->getHeaders(false)['content-type'][0] ?? '';
            $mediaType = MediaType::fromString($contentType);
            $data = json_decode($content, true);
        } catch (InvalidArgumentException $e) {
            return $content;
        }

        switch ((string) $mediaType) {
            case (string) new MediaType(ArticlesClient::TYPE_ARTICLE_HISTORY, Articles::VERSION_ARTICLE_HISTORY):
                $data['versions'] = $this->updateItems($data['versions']);
                break;

            case (string) new MediaType(ArticlesClient::TYPE_ARTICLE_LIST, Articles::VERSION_ARTICLE_LIST):
            case (string) new MediaType(CommunityClient::TYPE_COMMUNITY_LIST, Community::VERSION_COMMUNITY_LIST):
            case (string)new MediaType(SearchClient::TYPE_SEARCH, Search::VERSION_SEARCH):
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

        }

        return json_encode($data);
    }

    public function toArray(bool $throw = true): array
    {
        return json_decode($this->getContent($throw), true);
    }

    public function cancel(): void
    {
        $this->response->cancel();
    }

    public function getInfo(?string $type = null)
    {
        return $this->response->getInfo($type);
    }

    private function updateItems($items): array
    {
        return array_map([$this, 'updateItem'], $items);
    }

    private function updateItem($item)
    {
        if (isset($item['statusDate']) && isset($this->eraArticles[$item['id']])) {
            $item['statusDate'] = $this->eraArticles[$item['id']]['date'];
        }

        return $item;
    }
}