<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Collection\Sequence;
use eLife\Journal\Helper\Callback;
use eLife\Journal\Helper\ModelName;
use eLife\Journal\Helper\Paginator;
use eLife\Journal\Pagerfanta\SequenceAdapter;
use eLife\Patterns\ViewModel\AudioPlayer;
use eLife\Patterns\ViewModel\ContentHeader;
use eLife\Patterns\ViewModel\Highlight;
use eLife\Patterns\ViewModel\HighlightItem;
use eLife\Patterns\ViewModel\Link;
use eLife\Patterns\ViewModel\ListHeading;
use eLife\Patterns\ViewModel\ListingTeasers;
use eLife\Patterns\ViewModel\SectionListing;
use eLife\Patterns\ViewModel\SectionListingLink;
use eLife\Patterns\ViewModel\SeeMoreLink;
use eLife\Patterns\ViewModel\Teaser;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function GuzzleHttp\Promise\promise_for;

final class MagazineController extends Controller
{
    public function listAction(Request $request) : Response
    {
        $page = (int) $request->query->get('page', 1);
        $perPage = 10;

        $arguments = $this->defaultPageArguments($request);

        $latestResearch = promise_for($this->get('elife.api_sdk.search')
            ->forType('editorial', 'insight', 'feature', 'collection', 'interview', 'podcast-episode')
            ->sortBy('date'))
            ->then(function (Sequence $sequence) use ($page, $perPage) {
                $pagerfanta = new Pagerfanta(new SequenceAdapter($sequence, $this->willConvertTo(Teaser::class)));
                $pagerfanta->setMaxPerPage($perPage)->setCurrentPage($page);

                return $pagerfanta;
            });

        $arguments['title'] = 'Magazine';

        $arguments['paginator'] = $latestResearch
            ->then(function (Pagerfanta $pagerfanta) use ($request) {
                return new Paginator(
                    'Browse our latest Magazine content',
                    $pagerfanta,
                    function (int $page = null) use ($request) {
                        $routeParams = $request->attributes->get('_route_params');
                        $routeParams['page'] = $page;

                        return $this->get('router')->generate('magazine', $routeParams);
                    }
                );
            });

        $arguments['listing'] = $arguments['paginator']
            ->then($this->willConvertTo(ListingTeasers::class, ['type' => 'articles']));

        if (1 === $page) {
            return $this->createFirstPage($request, $arguments);
        }

        return $this->createSubsequentPage($request, $arguments);
    }

    private function createFirstPage(Request $request, array $arguments) : Response
    {
        $arguments['contentHeader'] = (new ContentHeader(
                'eLife Magazine',
                null,
                'Highlighting the latest research and giving a voice to scientists'
            ))->withSignupLink(new Link(
                'Sign up to eLife Magazine Highlights',
                'https://connect.elifesciences.org/magazine-highlights'
            ));

        $currentHighlights = $this->get('elife.api_sdk.highlights')
            ->getCurrent('magazine')
            ->map($this->willConvertTo(HighlightItem::class)); // calls HighlightHighlightItemConverter class

        $arguments['highlights'] = $currentHighlights->then(
            Callback::emptyOr(
                function () use ($currentHighlights) {
                    $heroHighlightItem = null;
                    if ($currentHighlights->count() === 4) {
                        $heroHighlightItem = $currentHighlights[0];
                        $highlightItems = $currentHighlights->slice(1, 3);
                    } else {
                        $highlightItems = $currentHighlights;
                    }
                    return new Highlight($highlightItems->toArray(), null, $heroHighlightItem);
                }
            )
        )->otherwise($this->softFailure('Failed to load highlights for magazine'));


        $events = $this->get('elife.api_sdk.events')
            ->show('open')
            ->reverse();

        $arguments['menuLink'] = new SectionListingLink('All sections', 'sections');

        $menu = [
            new Link(ModelName::plural('editorial'), $this->get('router')->generate('article-type', ['type' => 'editorial'])),
            new Link(ModelName::plural('insight'), $this->get('router')->generate('article-type', ['type' => 'insight'])),
            new Link(ModelName::plural('feature'), $this->get('router')->generate('article-type', ['type' => 'feature'])),
            new Link(ModelName::plural('podcast-episode'), $this->get('router')->generate('podcast')),
            new Link(ModelName::plural('collection'), $this->get('router')->generate('collections')),
            new Link('Digests', $this->get('router')->generate('digests')),
            new Link(ModelName::plural('interview'), $this->get('router')->generate('interviews')),
        ];

        $arguments['menu'] = new SectionListing('sections', $menu, new ListHeading('Magazine sections'), true);

        $arguments['events'] = $events
            ->slice(0, 3)
            ->then(Callback::emptyOr(function (Sequence $result) use ($events) {
                $items = $result->map($this->willConvertTo(Teaser::class, ['variant' => 'secondary']))->toArray();
                $heading = new ListHeading('Events');

                if (count($events) > 3) {
                    return ListingTeasers::withSeeMore(
                        $items,
                        new SeeMoreLink(new Link('See more events', $this->get('router')->generate('events'))),
                        $heading
                    );
                }

                return ListingTeasers::basic($items, $heading);
            }))
            ->otherwise($this->softFailure('Failed to load events'));

        $digests = $this->get('elife.api_sdk.digests');

        $arguments['digests'] = $digests
            ->slice(0, 3)
            ->then(Callback::emptyOr(function (Sequence $result) use ($digests) {
                $items = $result->map($this->willConvertTo(Teaser::class, ['variant' => 'secondary']))->toArray();
                $heading = new ListHeading('Digests');

                if (count($digests) > 3) {
                    return ListingTeasers::withSeeMore(
                        $items,
                        new SeeMoreLink(new Link('See more digests', $this->get('router')->generate('digests'))),
                        $heading
                    );
                }

                return ListingTeasers::basic($items, $heading);
            }))
            ->otherwise($this->softFailure('Failed to load digests'));

        return new Response($this->get('templating')->render('::magazine.html.twig', $arguments));
    }
}
