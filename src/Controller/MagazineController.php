<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\ApiClient\EventsClient;
use eLife\ApiSdk\ApiClient\MediumClient;
use eLife\ApiSdk\ApiClient\PodcastClient;
use eLife\ApiSdk\ApiClient\SearchClient;
use eLife\ApiSdk\MediaType;
use eLife\ApiSdk\Result;
use eLife\Patterns\ViewModel\AudioPlayer;
use eLife\Patterns\ViewModel\AudioSource;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use eLife\Patterns\ViewModel\Link;
use eLife\Patterns\ViewModel\ListHeading;
use eLife\Patterns\ViewModel\SeeMoreLink;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class MagazineController extends Controller
{
    public function listAction() : Response
    {
        $page = 1;
        $perPage = 6;

        $arguments = $this->defaultPageArguments();

        $arguments['contentHeader'] = ContentHeaderNonArticle::basic('Magazine', false,
            'Highlighting the latest research and giving a voice to life and biomedical scientists.');

        $arguments['audio_player'] = $this->get('elife.api_sdk.podcast')
            ->listEpisodes(['Accept' => new MediaType(PodcastClient::TYPE_PODCAST_EPISODE_LIST, 1)], 1, 1)
            ->then(function (Result $result) {
                if (empty($result['items'])) {
                    return null;
                }

                $item = $result['items'][0];

                return new AudioPlayer(
                    'Latest podcast: '.$item['title'],
                    [new AudioSource($item['mp3'], AudioSource::TYPE_MP3)]
                );
            })
            ->otherwise(function (Throwable $e) {
                return null;
            })
        ;

        $arguments['latestHeading'] = new ListHeading('Latest');
        $arguments['latest'] = $this->get('elife.api_sdk.search')
            ->query(['Accept' => new MediaType(SearchClient::TYPE_SEARCH, 1)], '', $page, $perPage, 'date', true, [],
                ['editorial', 'insight', 'feature', 'collection', 'interview', 'podcast-episode'])
            ->then(function (Result $result) use ($arguments) {
                if (empty($result['items'])) {
                    return null;
                }

                return $this->get('elife.journal.view_model.factory.listing_teaser')
                    ->forResult($result, $arguments['latestHeading']['heading']);
            });

        $arguments['events'] = $this->get('elife.api_sdk.events')
            ->listEvents(['Accept' => new MediaType(EventsClient::TYPE_EVENT_LIST, 1)], 1, 3, 'open', false)
            ->then(function (Result $result) {
                if (empty($result['items'])) {
                    return null;
                }

                $items = array_map(function (array $item) {
                    $item['type'] = 'event';

                    return $item;
                }, $result['items']);

                if ($result['total'] > 3) {
                    $seeMoreLink = new SeeMoreLink(
                        new Link('See more events', $this->get('router')->generate('events'))
                    );
                } else {
                    $seeMoreLink = null;
                }

                return $this->get('elife.journal.view_model.factory.listing_teaser_secondary')
                    ->forItems($items, 'Events', $seeMoreLink);
            })
            ->otherwise(function () {
                return null;
            });

        $arguments['elifeDigests'] = $this->get('elife.api_sdk.medium')
            ->listArticles(['Accept' => new MediaType(MediumClient::TYPE_MEDIUM_ARTICLE_LIST, 1)])
            ->then(function (Result $result) {
                if (empty($result['items'])) {
                    return null;
                }

                $items = array_map(function (array $item) {
                    $item['type'] = 'medium-article';

                    return $item;
                }, array_slice($result['items'], 0, 3));

                return $this->get('elife.journal.view_model.factory.listing_teaser_secondary')
                    ->forItems(
                        $items,
                        'eLife digests',
                        new SeeMoreLink(new Link('See more eLife digests on Medium', 'https://medium.com/@elife'))
                    );
            })
            ->otherwise(function () {
                return null;
            });

        return new Response($this->get('templating')->render('::magazine.html.twig', $arguments));
    }
}
