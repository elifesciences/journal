<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\PodcastEpisode;
use eLife\ApiSdk\Model\PodcastEpisodeChapter;
use eLife\Journal\Helper\Paginator;
use eLife\Journal\Pagerfanta\SequenceAdapter;
use eLife\Patterns\ViewModel\AudioPlayer;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use eLife\Patterns\ViewModel\ContentHeaderSimple;
use eLife\Patterns\ViewModel\GridListing;
use eLife\Patterns\ViewModel\LeadPara;
use eLife\Patterns\ViewModel\LeadParas;
use eLife\Patterns\ViewModel\Link;
use eLife\Patterns\ViewModel\ListingTeasers;
use eLife\Patterns\ViewModel\LoadMore;
use eLife\Patterns\ViewModel\MediaChapterListingItem;
use eLife\Patterns\ViewModel\Pager;
use eLife\Patterns\ViewModel\Teaser;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function GuzzleHttp\Promise\all;
use function GuzzleHttp\Promise\promise_for;

final class PodcastController extends Controller
{
    public function listAction(Request $request) : Response
    {
        $page = (int) $request->query->get('page', 1);
        $perPage = 6;

        $arguments = $this->defaultPageArguments();

        $episodes = promise_for($this->get('elife.api_sdk.podcast_episodes'))
            ->then(function (Sequence $sequence) use ($page, $perPage) {
                $pagerfanta = new Pagerfanta(new SequenceAdapter($sequence));
                $pagerfanta->setMaxPerPage($perPage)->setCurrentPage($page);

                return $pagerfanta;
            });

        $arguments['paginator'] = $episodes
            ->then(function (Pagerfanta $pagerfanta) use ($request) {
                return new Paginator($pagerfanta, function (int $page = null) use ($request) {
                    $routeParams = $request->attributes->get('_route_params');
                    $routeParams['page'] = $page;

                    return $this->get('router')->generate('podcast', $routeParams);
                });
            });

        $arguments['episodes'] = $episodes
            ->then(function (Pagerfanta $pagerfanta) {
                return new ArraySequence(iterator_to_array($pagerfanta));
            });

        if (1 === $page) {
            return $this->createFirstPage($arguments);
        }

        return $this->createSubsequentPage($arguments);
    }

    private function createFirstPage(array $arguments) : Response
    {
        $arguments['contentHeader'] = ContentHeaderNonArticle::basic('eLife podcast');

        $arguments['episodes'] = all(['episodes' => $arguments['episodes'], 'paginator' => $arguments['paginator']])
            ->then(function (array $parts) {
                $episodes = $parts['episodes'];
                $paginator = $parts['paginator'];

                if ($episodes->isEmpty()) {
                    return null;
                }

                $teasers = $episodes->map(function (PodcastEpisode $episode) {
                    return $this->get('elife.journal.view_model.converter')->convert($episode, Teaser::class, ['variant' => 'grid']);
                })->toArray();

                if ($paginator->getNextPage()) {
                    return GridListing::forTeasers(
                        $teasers,
                        'Latest episodes',
                        $paginator->getNextPage() ? new LoadMore(new Link('Load more episodes', $paginator->getNextPagePath())) : null
                    );
                }

                return GridListing::forTeasers($teasers, 'Experiments');
            });

        return new Response($this->get('templating')->render('::podcast.html.twig', $arguments));
    }

    private function createSubsequentPage(array $arguments) : Response
    {
        $arguments['contentHeader'] = $arguments['paginator']
            ->then(function (Paginator $paginator) {
                return new ContentHeaderSimple(
                    'Browse the podcast',
                    sprintf('Page %s of %s', number_format($paginator->getCurrentPage()), number_format(count($paginator)))
                );
            });

        $arguments['episodes'] = all(['episodes' => $arguments['episodes'], 'paginator' => $arguments['paginator']])
            ->then(function (array $parts) {
                $episodes = $parts['episodes'];
                $paginator = $parts['paginator'];

                return GridListing::forTeasers(
                    $episodes->map(function (PodcastEpisode $episode) {
                        return $this->get('elife.journal.view_model.converter')->convert($episode, Teaser::class, ['variant' => 'grid']);
                    })->toArray(),
                    null,
                    new Pager(
                        $paginator->getPreviousPage() ? new Link('Newer', $paginator->getPreviousPagePath()) : null,
                        $paginator->getNextPage() ? new Link('Older', $paginator->getNextPagePath()) : null
                    )
                );
            });

        return new Response($this->get('templating')->render('::podcast-alt.html.twig', $arguments));
    }

    public function episodeAction(int $number) : Response
    {
        $arguments = $this->defaultPageArguments();

        $arguments['episode'] = $this->get('elife.api_sdk.podcast_episodes')->get($number);

        $arguments['contentHeader'] = $arguments['episode']
            ->then(function (PodcastEpisode $episode) {
                return $this->get('elife.journal.view_model.converter')->convert($episode, ContentHeaderNonArticle::class);
            });

        $arguments['audioPlayer'] = $arguments['episode']
            ->then(function (PodcastEpisode $episode) {
                return $this->get('elife.journal.view_model.converter')->convert($episode, AudioPlayer::class);
            });

        $arguments['leadParas'] = $arguments['episode']
            ->then(function (PodcastEpisode $episode) {
                return new LeadParas([new LeadPara($episode->getImpactStatement())]);
            })
            ->otherwise(function () {
                return null;
            });

        $arguments['chapters'] = $arguments['episode']
            ->then(function (PodcastEpisode $episode) {
                return $episode->getChapters()->map(function (PodcastEpisodeChapter $chapter) {
                    return $this->get('elife.journal.view_model.converter')->convert($chapter, MediaChapterListingItem::class);
                });
            });

        $arguments['related'] = $arguments['episode']
            ->then(function (PodcastEpisode $episode) {
                $articles = [];

                foreach ($episode->getChapters() as $chapter) {
                    $articles = array_merge($articles, $chapter->getContent()->slice(0, 1)->toArray());
                }

                if (empty($articles)) {
                    return null;
                }

                return ListingTeasers::basic(
                    array_map(function (Model $model) {
                        return $this->get('elife.journal.view_model.converter')->convert($model, Teaser::class, ['variant' => 'secondary']);
                    }, $articles),
                    'Related'
                );
            });

        return new Response($this->get('templating')->render('::podcast-episode.html.twig', $arguments));
    }
}
