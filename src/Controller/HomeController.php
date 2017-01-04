<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Subject;
use eLife\Journal\Helper\Callback;
use eLife\Journal\Helper\Paginator;
use eLife\Journal\Pagerfanta\SequenceAdapter;
use eLife\Patterns\ViewModel\AllSubjectsList;
use eLife\Patterns\ViewModel\AllSubjectsListLink;
use eLife\Patterns\ViewModel\Carousel;
use eLife\Patterns\ViewModel\ContentHeaderSimple;
use eLife\Patterns\ViewModel\LeadPara;
use eLife\Patterns\ViewModel\LeadParas;
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
use function GuzzleHttp\Promise\all;
use function GuzzleHttp\Promise\promise_for;

final class HomeController extends Controller
{
    public function homeAction(Request $request) : Response
    {
        $page = (int) $request->query->get('page', 1);
        $perPage = 6;

        $arguments = $this->defaultPageArguments();

        $latestResearch = promise_for($this->get('elife.api_sdk.search')
            ->forType('research-advance', 'research-article', 'research-exchange', 'short-report', 'tools-resources', 'replication-study')
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

                    return $this->get('router')->generate('home', $routeParams);
                });
            });

        $arguments['latestResearch'] = $latestResearch
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
        $arguments['carousel'] = $this->get('elife.api_sdk.covers')
            ->getCurrent()
            ->map($this->willConvertTo())
            ->then(function (Sequence $covers) {
                return new Carousel(...$covers);
            })
            ->otherwise(function () {
                return null;
            });

        $arguments['leadParas'] = new LeadParas([new LeadPara('eLife is an open-access journal that publishes research in the life and biomedical sciences', 'strapline')]);

        $arguments['subjectsLink'] = new AllSubjectsListLink('subjects');

        $arguments['subjects'] = $this->get('elife.api_sdk.subjects')
            ->reverse()
            ->slice(1, 100)
            ->map(function (Subject $subject) {
                return new Link($subject->getName(), $this->get('router')->generate('subject', ['id' => $subject->getId()]));
            })
            ->then(function (Sequence $links) {
                return new AllSubjectsList('subjects', $links->toArray(), 'strapline');
            })
            ->otherwise(function () {
                return null;
            });

        $arguments['latestResearchHeading'] = new ListHeading($latestResearchHeading = 'Latest research');
        $arguments['latestResearch'] = all(['latestResearch' => $arguments['latestResearch'], 'paginator' => $arguments['paginator']])
            ->then(function (array $parts) use ($latestResearchHeading) {
                $latestResearch = $parts['latestResearch'];
                $paginator = $parts['paginator'];

                if ($latestResearch->isEmpty()) {
                    return null;
                }

                $teasers = $latestResearch->map($this->willConvertTo(Teaser::class))->toArray();

                if ($paginator->getNextPage()) {
                    return ListingTeasers::withPagination(
                        $teasers,
                        $paginator->getNextPage() ? new LoadMore(new Link('More articles', $paginator->getNextPagePath())) : null,
                        $latestResearchHeading
                    );
                }

                return ListingTeasers::basic($teasers, $latestResearchHeading);
            });

        $arguments['magazine'] = $this->get('elife.api_sdk.search')
            ->forType('editorial', 'insight', 'feature', 'collection', 'interview', 'podcast-episode')
            ->sortBy('date')
            ->slice(1, 7)
            ->then(Callback::emptyOr(function (Sequence $result) {
                return ListingTeasers::withSeeMore(
                    $result->map($this->willConvertTo(Teaser::class, ['variant' => 'secondary']))->toArray(),
                    new SeeMoreLink(new Link('See more Magazine articles', $this->get('router')->generate('magazine'))),
                    'Magazine'
                );
            }))->otherwise(function () {
                return null;
            });

        return new Response($this->get('templating')->render('::home.html.twig', $arguments));
    }

    private function createSubsequentPage(array $arguments) : Response
    {
        $arguments['contentHeader'] = $arguments['paginator']
            ->then(function (Paginator $paginator) {
                return new ContentHeaderSimple(
                    'Browse our latest research',
                    sprintf('Page %s of %s', number_format($paginator->getCurrentPage()), number_format(count($paginator)))
                );
            });

        $arguments['latestResearch'] = all(['latestResearch' => $arguments['latestResearch'], 'paginator' => $arguments['paginator']])
            ->then(function (array $parts) {
                $latestResearch = $parts['latestResearch'];
                $paginator = $parts['paginator'];

                return ListingTeasers::withPagination(
                    $latestResearch->map($this->willConvertTo(Teaser::class))->toArray(),
                    new Pager(
                        $paginator->getPreviousPage() ? new Link('Newer articles', $paginator->getPreviousPagePath()) : null,
                        $paginator->getNextPage() ? new Link('Older articles', $paginator->getNextPagePath()) : null
                    )
                );
            });

        return new Response($this->get('templating')->render('::home-alt.html.twig', $arguments));
    }
}
