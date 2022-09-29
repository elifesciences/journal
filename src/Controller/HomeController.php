<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Cover;
use eLife\ApiSdk\Model\Subject;
use eLife\Journal\Helper\Callback;
use eLife\Journal\Helper\Paginator;
use eLife\Journal\Pagerfanta\SequenceAdapter;
use eLife\Patterns\ViewModel\Carousel;
use eLife\Patterns\ViewModel\CarouselItem;
use eLife\Patterns\ViewModel\HeroBanner;
use eLife\Patterns\ViewModel\Highlight;
use eLife\Patterns\ViewModel\HighlightItem;
use eLife\Patterns\ViewModel\LeadPara;
use eLife\Patterns\ViewModel\LeadParas;
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

final class HomeController extends Controller
{
    public function homeAction(Request $request) : Response
    {
        $page = (int) $request->query->get('page', 1);
        $perPage = 10;

        $arguments = $this->defaultPageArguments($request);

        $latestResearch = promise_for($this->get('elife.api_sdk.search')
            ->forType('research-advance', 'research-article', 'research-communication', 'review-article', 'scientific-correspondence', 'short-report', 'tools-resources', 'replication-study')
            ->sortBy('date'))
            ->then(function (Sequence $sequence) use ($page, $perPage) {
                $pagerfanta = new Pagerfanta(new SequenceAdapter($sequence, $this->willConvertTo(Teaser::class)));
                $pagerfanta->setMaxPerPage($perPage)->setCurrentPage($page);

                return $pagerfanta;
            });

        $arguments['title'] = 'Latest research';

        $arguments['paginator'] = $latestResearch
            ->then(function (Pagerfanta $pagerfanta) use ($request) {
                return new Paginator(
                    'Browse our latest research',
                    $pagerfanta,
                    function (int $page = null) use ($request) {
                        $routeParams = $request->attributes->get('_route_params');
                        $routeParams['page'] = $page;

                        return $this->get('router')->generate('home', $routeParams);
                    }
                );
            });

        $arguments['listing'] = $arguments['paginator']
            ->then($this->willConvertTo(ListingTeasers::class, ['heading' => 'Latest research', 'type' => 'articles']));

        if (1 === $page) {
            return $this->createFirstPage($arguments);
        }

        return $this->createSubsequentPage($request, $arguments);
    }

    private function createFirstPage(array $arguments) : Response
    {
        if ($arguments['isHero']) {
            $covers =  $this->get('elife.api_sdk.covers')->getCurrent();
            $arguments['heroBanner'] = $covers
                ->then(function (Sequence $items) {
                    /** @var Cover $item */
                    $cover = $items[0];

                    return $this->convertTo($cover, HeroBanner::class);
                })
//                ->otherwise($this->softFailure('Failed to load covers'))
            ;

            $arguments['highlights'] = $covers
                ->map($this->willConvertTo(HighlightItem::class))
                ->then(Callback::emptyOr(function (Sequence $covers) {
                    return new Highlight($covers->slice(1, 4)->toArray(), new ListHeading('Highlights', 'highlights'));
                }));
        } else {
            $arguments['carousel'] = $this->get('elife.api_sdk.covers')
                ->getCurrent()
                ->map($this->willConvertTo(CarouselItem::class))
                ->then(Callback::emptyOr(function (Sequence $covers) {
                    return new Carousel($covers->slice(0, 3)->toArray(), new ListHeading('Highlights', 'highlights'));
                }))
                ->otherwise($this->softFailure('Failed to load covers'));
        }

        $arguments['leadParas'] = new LeadParas([new LeadPara('eLife works to improve research communication through open science and open technology innovation', 'strapline')]);

        $arguments['subjectsLink'] = new SectionListingLink('All research categories', 'subjects');

        $arguments['subjects'] = $this->get('elife.api_sdk.subjects')
            ->reverse()
            ->slice(1, 100)
            ->map(function (Subject $subject) {
                return new Link($subject->getName(), $this->get('router')->generate('subject', [$subject]));
            })
            ->then(function (Sequence $links) {
                return new SectionListing('subjects', $links->toArray(), new ListHeading('Research categories'), false, 'strapline');
            })
            ->otherwise($this->softFailure('Failed to load subjects list'));

        $arguments['announcements'] = $this->get('elife.api_sdk.highlights')
            ->get('announcements')
            ->slice(0, 3)
            ->map($this->willConvertTo(Teaser::class, ['variant' => 'secondary']))
            ->then(Callback::emptyOr(function (Sequence $highlights) {
                return ListingTeasers::basic($highlights->toArray(), new ListHeading('New from eLife'));
            }))
            ->otherwise($this->softFailure('Failed to load announcements'));

        $arguments['magazine'] = $this->get('elife.api_sdk.search')
            ->forType('editorial', 'insight', 'feature', 'collection', 'interview', 'podcast-episode')
            ->sortBy('date')
            ->slice(1, 7)
            ->then(Callback::emptyOr(function (Sequence $result) {
                return ListingTeasers::withSeeMore(
                    $result->map($this->willConvertTo(Teaser::class, ['variant' => 'secondary']))->toArray(),
                    new SeeMoreLink(new Link('See more Magazine articles', $this->get('router')->generate('magazine'))),
                    new ListHeading('Magazine')
                );
            }))
            ->otherwise($this->softFailure('Failed to load Magazine list'));

        return new Response($this->get('templating')->render('::home.html.twig', $arguments));
    }
}
