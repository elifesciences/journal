<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Cover;
use eLife\ApiSdk\Model\Subject;
use eLife\Journal\Helper\Callback;
use eLife\Journal\Helper\Paginator;
use eLife\Journal\Pagerfanta\SequenceAdapter;
use eLife\Patterns\ViewModel\Button;
use eLife\Patterns\ViewModel\HeroBanner;
use eLife\Patterns\ViewModel\Highlight;
use eLife\Patterns\ViewModel\HighlightItem;
use eLife\Patterns\ViewModel\HomeBanner;
use eLife\Patterns\ViewModel\Link;
use eLife\Patterns\ViewModel\ListHeading;
use eLife\Patterns\ViewModel\ListingTeasers;
use eLife\Patterns\ViewModel\SectionListing;
use eLife\Patterns\ViewModel\SectionListingLink;
use eLife\Patterns\ViewModel\SeeMoreLink;
use eLife\Patterns\ViewModel\SimpleImage;
use eLife\Patterns\ViewModel\Teaser;
use eLife\Patterns\ViewModel\TestimonialWithLink;
use eLife\Patterns\ViewModel\ViewSelector;
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

        $arguments = $this->defaultPageArguments($request, null, true);

        $searchTypes = [
            'reviewed-preprint',
            'research-advance',
            'research-article',
            'research-communication',
            'review-article',
            'scientific-correspondence',
            'short-report',
            'tools-resources',
            'replication-study',
        ];

        $latestResearch = promise_for($this->get('elife.api_sdk.search')
            ->forType(...$searchTypes)
            ->sortBy('date'))
            ->then(function (Sequence $sequence) use ($page, $perPage) {
                $pagerfanta = new Pagerfanta(new SequenceAdapter($sequence, $this->willConvertTo(Teaser::class)));
                $pagerfanta->setMaxPerPage($perPage)->setCurrentPage($page);

                return $pagerfanta;
            });

        $arguments['title'] = 'Latest research';

        $arguments['description'] = 'eLife works to improve research communication through open science and open technology innovation';

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
        $arguments['homeBanner'] = new HomeBanner(
            Button::homeBanner('Browse the latest research', '/browse'),
            Button::homeBanner('Learn more about eLife', '/about', 'medium', 'secondary')
        );

        $heroHighlights = $this->get('elife.api_sdk.covers')
            ->getCurrent()
            ->then(function (Sequence $items) {
                return $items
                    ->slice(0, 6)
                    ->map(function(Cover $cover) {
                        return $this->convertTo(
                            $cover,
                            HighlightItem::class
                        );
                    });
            })->otherwise($this->softFailure('Failed to load hero and highlights'));

        $arguments['highlights'] = $heroHighlights->then(function (Sequence $covers) {
            return $covers->filter(Callback::isInstanceOf(HighlightItem::class));
        })->then(Callback::emptyOr(function (Sequence $highlights) {
            return new Highlight($highlights->toArray(), new ListHeading('Highlights', 'highlights'));
        }))->otherwise($this->softFailure('Failed to load hero and highlights'));

        $arguments['subjectsLink'] = new SectionListingLink('All research categories', 'subjects');
        
        $arguments['testimonialWithLink'] = new TestimonialWithLink(
            new SimpleImage(
                'https://iiif.elifesciences.org/journal-cms/person%2F2025-09%2Fpatrick-allard-from-source.jpg/0,0,512,512/320,/0/default.webp',
                'Headshot of Patrick Allard'
            ),
            "We liked the idea of having an open 'conversation' with the reviewers during the process, and having the chance to polish the manuscript by following the editors and reviewers’ recommendations without the threat of rejection.",
            'Patrick Allard, UCLA and eLife author',
            new Link(
                'Researchers explain why they published in eLife',
                'https://elifesciences.org/about/why-publish-with-elife'
            )
        );

        $arguments['subjects'] = $this->get('elife.api_sdk.subjects')
            ->reverse()
            ->slice(1, 100)
            ->map(function (Subject $subject) {
                return new Link($subject->getName(), $this->get('router')->generate('subject', [$subject]));
            })
            ->then(function (Sequence $links) {
                return new SectionListing('subjects', $links->toArray(), new ListHeading('Categories'), false, true);
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

        $arguments['viewSelector'] = new ViewSelector(
            new Link('Latest research', '#primaryListing'),
            [],
            new Link('Magazine', '#secondaryListing'),
            false,
            true
        );

        return new Response($this->get('templating')->render('::home.html.twig', $arguments));
    }
}
