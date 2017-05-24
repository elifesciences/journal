<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\PodcastEpisode;
use eLife\Journal\Helper\Callback;
use eLife\Journal\Helper\Paginator;
use eLife\Journal\Pagerfanta\SequenceAdapter;
use eLife\Patterns\ViewModel\AudioPlayer;
use eLife\Patterns\ViewModel\ContentHeader;
use eLife\Patterns\ViewModel\GridListing;
use eLife\Patterns\ViewModel\ListHeading;
use eLife\Patterns\ViewModel\ListingTeasers;
use eLife\Patterns\ViewModel\MediaChapterListingItem;
use eLife\Patterns\ViewModel\Teaser;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function GuzzleHttp\Promise\promise_for;

final class PodcastController extends Controller
{
    public function listAction(Request $request) : Response
    {
        $page = (int) $request->query->get('page', 1);
        $perPage = 8;

        $arguments = $this->defaultPageArguments($request);

        $episodes = promise_for($this->get('elife.api_sdk.podcast_episodes'))
            ->then(function (Sequence $sequence) use ($page, $perPage) {
                $pagerfanta = new Pagerfanta(new SequenceAdapter($sequence, $this->willConvertTo(Teaser::class, ['variant' => 'grid'])));
                $pagerfanta->setMaxPerPage($perPage)->setCurrentPage($page);

                return $pagerfanta;
            });

        $arguments['title'] = 'Podcast';

        $arguments['paginator'] = $episodes
            ->then(function (Pagerfanta $pagerfanta) use ($request) {
                return new Paginator(
                    'Browse the podcast',
                    $pagerfanta,
                    function (int $page = null) use ($request) {
                        $routeParams = $request->attributes->get('_route_params');
                        $routeParams['page'] = $page;

                        return $this->get('router')->generate('podcast', $routeParams);
                    }
                );
            });

        $arguments['listing'] = $arguments['paginator']
            ->then($this->willConvertTo(GridListing::class, ['type' => 'episodes']));

        if (1 === $page) {
            return $this->createFirstPage($arguments);
        }

        return $this->createSubsequentPage($request, $arguments);
    }

    private function createFirstPage(array $arguments) : Response
    {
        $arguments['contentHeader'] = new ContentHeader('eLife podcast');

        return new Response($this->get('templating')->render('::podcast.html.twig', $arguments));
    }

    public function episodeAction(Request $request, int $number) : Response
    {
        $episode = $this->get('elife.api_sdk.podcast_episodes')
            ->get($number)
            ->otherwise($this->mightNotExist());

        $arguments = $this->defaultPageArguments($request, $episode);

        $arguments['title'] = $episode
            ->then(Callback::method('getTitle'));

        $arguments['episode'] = $episode;

        $arguments['contentHeader'] = $arguments['episode']
            ->then($this->willConvertTo(ContentHeader::class));

        $arguments['audioPlayer'] = $arguments['episode']
            ->then($this->willConvertTo(AudioPlayer::class));

        $arguments['chapters'] = $arguments['episode']
            ->then(function (PodcastEpisode $episode) {
                return $episode->getChapters()->map($this->willConvertTo(MediaChapterListingItem::class));
            });

        $arguments['related'] = $arguments['episode']
            ->then(function (PodcastEpisode $episode) {
                return $episode->getChapters()
                    ->map(Callback::method('getContent'))
                    ->map(Callback::emptyOr(Callback::method('offsetGet', 0)))
                    ->filter();
            })
            ->then(Callback::emptyOr(function (Sequence $articles) {
                return ListingTeasers::basic(
                    $articles->map($this->willConvertTo(Teaser::class, ['variant' => 'secondary']))->toArray(),
                    new ListHeading('Related')
                );
            }));

        return new Response($this->get('templating')->render('::podcast-episode.html.twig', $arguments));
    }
}
