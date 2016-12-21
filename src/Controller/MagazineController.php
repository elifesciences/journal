<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Model;
use eLife\Journal\Helper\Paginator;
use eLife\Journal\Pagerfanta\SequenceAdapter;
use eLife\Patterns\ViewModel\AudioPlayer;
use eLife\Patterns\ViewModel\BackgroundImage;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use eLife\Patterns\ViewModel\ContentHeaderSimple;
use eLife\Patterns\ViewModel\Link;
use eLife\Patterns\ViewModel\ListHeading;
use eLife\Patterns\ViewModel\ListingTeasers;
use eLife\Patterns\ViewModel\LoadMore;
use eLife\Patterns\ViewModel\Pager;
use eLife\Patterns\ViewModel\SeeMoreLink;
use eLife\Patterns\ViewModel\Teaser;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use function GuzzleHttp\Promise\all;
use function GuzzleHttp\Promise\promise_for;

final class MagazineController extends Controller
{
    public function listAction(Request $request) : Response
    {
        $page = (int) $request->query->get('page', 1);
        $perPage = 6;

        $arguments = $this->defaultPageArguments();

        $latestResearch = promise_for($this->get('elife.api_sdk.search')
            ->forType('editorial', 'insight', 'feature', 'collection', 'interview', 'podcast-episode')
            ->sortBy('date'))
            ->then(function (Sequence $sequence) use ($page, $perPage) {
                $pagerfanta = new Pagerfanta(new SequenceAdapter($sequence));
                $pagerfanta->setMaxPerPage($perPage)->setCurrentPage($page);

                return $pagerfanta;
            });

        $arguments['paginator'] = $latestResearch
            ->then(function (Pagerfanta $pagerfanta) use ($request) {
                return new Paginator($pagerfanta, function (int $page = null) use ($request) {
                    $routeParams = $request->attributes->get('_route_params');
                    $routeParams['page'] = $page;

                    return $this->get('router')->generate('magazine', $routeParams);
                });
            });

        $arguments['latest'] = $latestResearch
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
        $arguments['contentHeader'] = ContentHeaderNonArticle::basic('Magazine', true,
            'Highlighting the latest research and giving a voice to life and biomedical scientists.',
            null, null, new BackgroundImage(
                $this->get('puli.url_generator')->generateUrl('/elife/journal/images/banners/magazine-lo-res.jpg'),
                $this->get('puli.url_generator')->generateUrl('/elife/journal/images/banners/magazine-hi-res.jpg')
            ));

        $arguments['latestHeading'] = new ListHeading($latestHeading = 'Latest');
        $arguments['latest'] = all(['latest' => $arguments['latest'], 'paginator' => $arguments['paginator']])
            ->then(function (array $parts) use ($latestHeading) {
                $latest = $parts['latest'];
                $paginator = $parts['paginator'];

                if ($latest->isEmpty()) {
                    return null;
                }

                $teasers = $latest->map(function (Model $model) {
                    return $this->get('elife.journal.view_model.converter')->convert($model, Teaser::class);
                })->toArray();

                if ($paginator->getNextPage()) {
                    return ListingTeasers::withPagination(
                        $teasers,
                        $paginator->getNextPage() ? new LoadMore(new Link('Load more', $paginator->getNextPagePath())) : null,
                        $latestHeading
                    );
                }

                return ListingTeasers::basic($teasers, $latestHeading);
            });

        $arguments['audio_player'] = $this->get('elife.api_sdk.podcast_episodes')
            ->slice(0, 1)
            ->then(function (Sequence $result) {
                if ($result->isEmpty()) {
                    return null;
                }

                return $this->get('elife.journal.view_model.converter')->convert($result[0], AudioPlayer::class);
            })
            ->otherwise(function (Throwable $e) {
                return null;
            });

        $events = $this->get('elife.api_sdk.events')
            ->forType('open')
            ->reverse();

        $arguments['events'] = $events
            ->slice(0, 3)
            ->then(function (Sequence $result) use ($events) {
                if ($result->isEmpty()) {
                    return null;
                }

                $items = $result->map(function (Model $model) {
                    return $this->get('elife.journal.view_model.converter')->convert($model, Teaser::class, ['variant' => 'secondary']);
                })->toArray();
                $heading = 'Events';

                if (count($events) > 3) {
                    return ListingTeasers::withSeeMore(
                        $items,
                        new SeeMoreLink(new Link('See more events', $this->get('router')->generate('events'))),
                        $heading
                    );
                }

                return ListingTeasers::basic($items, $heading);
            })
            ->otherwise(function () {
                return null;
            });

        $arguments['elifeDigests'] = $this->get('elife.api_sdk.medium_articles')
            ->slice(0, 3)
            ->then(function (Sequence $result) {
                if ($result->isEmpty()) {
                    return null;
                }

                return ListingTeasers::withSeeMore(
                    $result->map(function (Model $model) {
                        return $this->get('elife.journal.view_model.converter')->convert($model, Teaser::class, ['variant' => 'secondary']);
                    })->toArray(),
                    new SeeMoreLink(new Link('See more eLife digests on Medium', 'https://medium.com/@elife')),
                    'eLife digests'
                );
            })->otherwise(function () {
                return null;
            });

        return new Response($this->get('templating')->render('::magazine.html.twig', $arguments));
    }

    private function createSubsequentPage(array $arguments) : Response
    {
        $arguments['contentHeader'] = $arguments['paginator']
            ->then(function (Paginator $paginator) {
                return new ContentHeaderSimple(
                    'Browse our latest Magazine content',
                    sprintf('Page %s of %s', number_format($paginator->getCurrentPage()), number_format(count($paginator)))
                );
            });

        $arguments['latest'] = all(['latest' => $arguments['latest'], 'paginator' => $arguments['paginator']])
            ->then(function (array $parts) {
                $latest = $parts['latest'];
                $paginator = $parts['paginator'];

                return ListingTeasers::withPagination(
                    $latest->map(function (Model $model) {
                        return $this->get('elife.journal.view_model.converter')->convert($model, Teaser::class);
                    })->toArray(),
                    new Pager(
                        $paginator->getPreviousPage() ? new Link('Newer', $paginator->getPreviousPagePath()) : null,
                        $paginator->getNextPage() ? new Link('Older', $paginator->getNextPagePath()) : null
                    )
                );
            });

        return new Response($this->get('templating')->render('::magazine-alt.html.twig', $arguments));
    }
}
