<?php

namespace eLife\Journal\Guzzle;

use eLife\ApiClient\ApiClient\CollectionsClient;
use eLife\ApiClient\ApiClient\CommunityClient;
use eLife\ApiClient\ApiClient\CoversClient;
use eLife\ApiClient\ApiClient\HighlightsClient;
use eLife\ApiClient\ApiClient\PodcastClient;
use eLife\ApiClient\ApiClient\PromotionalCollectionsClient;
use eLife\ApiClient\ApiClient\SearchClient;
use eLife\ApiClient\MediaType;
use eLife\ApiSdk\Client\Collections;
use eLife\ApiSdk\Client\Community;
use eLife\ApiSdk\Client\Covers;
use eLife\ApiSdk\Client\Highlights;
use eLife\ApiSdk\Client\PodcastEpisodes;
use eLife\ApiSdk\Client\PromotionalCollections;
use eLife\ApiSdk\Client\Search;
use InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use function GuzzleHttp\json_decode;
use function GuzzleHttp\json_encode;
use function GuzzleHttp\Promise\promise_for;
use function GuzzleHttp\Psr7\stream_for;

final class PodcastEpisodeMp3RewritingMiddleware
{

    public function __invoke(callable $handler) : callable
    {
        return function (RequestInterface $request, array $options = []) use (&$handler) {
            return promise_for($handler($request, $options))->then(function (ResponseInterface $response) use ($request) {
                try {
                    $mediaType = MediaType::fromString($response->getHeaderLine('Content-Type'));
                    $data = json_decode($response->getBody(), true);
                } catch (InvalidArgumentException $e) {
                    return $response;
                }

                switch ((string) $mediaType) {
                    case (string) new MediaType(CollectionsClient::TYPE_COLLECTION, Collections::VERSION_COLLECTION):
                    case (string) new MediaType(PromotionalCollectionsClient::TYPE_PROMOTIONAL_COLLECTION, PromotionalCollections::VERSION_PROMOTIONAL_COLLECTION):
                        $data['podcastEpisodes'] = $this->updateItems($data['podcastEpisodes'] ?? []);
                        break;

                    case (string) new MediaType(CoversClient::TYPE_COVERS_LIST, Covers::VERSION_COVERS_LIST):
                    case (string) new MediaType(HighlightsClient::TYPE_HIGHLIGHT_LIST, Highlights::VERSION_HIGHLIGHT_LIST):
                        $data['items'] = array_map(function (array $highlight) {
                            $highlight['item'] = $this->updateItem($highlight['item']);

                            return $highlight;
                        }, $data['items']);
                        break;

                    case (string) new MediaType(CommunityClient::TYPE_COMMUNITY_LIST, Community::VERSION_COMMUNITY_LIST):
                    case (string) new MediaType(PodcastClient::TYPE_PODCAST_EPISODE_LIST, PodcastEpisodes::VERSION_PODCAST_EPISODE_LIST):
                    case (string) new MediaType(SearchClient::TYPE_SEARCH, Search::VERSION_SEARCH):
                        $data['items'] = $this->updateItems($data['items']);
                        break;

                    case (string) new MediaType(PodcastClient::TYPE_PODCAST_EPISODE, PodcastEpisodes::VERSION_PODCAST_EPISODE):
                        $data = $this->updateItem($data);
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
        if (!empty($item['sources'])) {
            $item['sources'] = array_map(function ($source) {
                if (isset($source['uri'])) {
                    $url = parse_url($source['uri'], PHP_URL_PATH);
                    if ('mp3' === pathinfo(parse_url($url,PHP_URL_PATH),PATHINFO_EXTENSION)) {
                        $file_name = basename($url);

                        $source['uri'] = 'https://downloads.nakeddiscovery.com/downloads/active/'.$file_name;
                    }
                }

                return $source;
            }, $item['sources']);
        }

        return $item;
    }
}
