<?php

namespace eLife\Journal\Controller;

use eLife\ApiClient\Result;
use eLife\ApiSdk\Client\Search;
use eLife\ApiSdk\Collection\Sequence;
use eLife\Journal\Helper\Callback;
use eLife\Journal\Helper\Paginator;
use eLife\Journal\Pagerfanta\SequenceAdapter;
use eLife\Patterns\ViewModel\Link;
use eLife\Patterns\ViewModel\ListingTeasers;
use eLife\Patterns\ViewModel\MessageBar;
use eLife\Patterns\ViewModel\SortControl;
use eLife\Patterns\ViewModel\SortControlOption;
use eLife\Patterns\ViewModel\Teaser;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function GuzzleHttp\Promise\all;
use function GuzzleHttp\Promise\promise_for;

final class SearchController extends Controller
{
    public function queryAction(Request $request) : Response
    {
        $page = (int) $request->query->get('page', 1);
        $perPage = 6;

        $for = trim($request->query->get('for'));
        $subjects = $request->query->get('subjects', []);
        $sort = $request->query->get('sort', 'relevance');
        $order = $request->query->get('order', SortControlOption::DESC);

        $arguments = $this->defaultPageArguments();

        $search = $this->get('elife.api_sdk.search')
            ->forQuery($for)
            ->forSubject(...$subjects)
            ->sortBy($sort);

        if (SortControlOption::ASC === $order) {
            $search = $search->reverse();
        }

        $search = promise_for($search)
            ->then(function (Sequence $sequence) use ($page, $perPage) {
                $pagerfanta = new Pagerfanta(new SequenceAdapter($sequence, $this->willConvertTo(Teaser::class)));
                $pagerfanta->setMaxPerPage($perPage)->setCurrentPage($page);

                return $pagerfanta;
            });

        $arguments['title'] = 'Search';

        $arguments['paginator'] = $search
            ->then(function (Pagerfanta $pagerfanta) use ($request) {
                return new Paginator(
                    'Browse the search results',
                    $pagerfanta,
                    function (int $page = null) use ($request) {
                        $routeParams = $request->query->all();
                        $routeParams['page'] = $page;

                        return $this->get('router')->generate('search', $routeParams);
                    }
                );
            });

        $arguments['listing'] = $arguments['paginator']
            ->then(Callback::methodEmptyOr('getTotal', $this->willConvertTo(ListingTeasers::class, ['heading' => ''])));

        if (1 === $page) {
            return $this->createFirstPage($request, $arguments);
        }

        return $this->createSubsequentPage($request, $arguments);
    }

    private function createFirstPage(Request $request, array $arguments) : Response
    {
        $order = $request->query->get('order', SortControlOption::DESC);

        $arguments['messageBar'] = $arguments['paginator']
            ->then(function (Paginator $paginator) {
                if (1 === $paginator->getTotal()) {
                    return new MessageBar('1 result found');
                }

                return new MessageBar(number_format($paginator->getTotal()).' results found');
            });

        $relevanceQuery = clone $request->query;
        $relevanceQuery->set('sort', 'relevance');
        $relevanceQuery->set('order', SortControlOption::ASC === $order ? SortControlOption::DESC : SortControlOption::ASC);

        $dateQuery = clone $request->query;
        $dateQuery->set('sort', 'date');
        $dateQuery->set('order', SortControlOption::ASC === $order ? SortControlOption::DESC : SortControlOption::ASC);

        $arguments['sortControl'] = new SortControl([
            new SortControlOption(
                new Link('Relevance', $this->get('router')->generate('search', $relevanceQuery->all())),
                SortControlOption::ASC === $order ? SortControlOption::ASC : SortControlOption::DESC
            ),
            new SortControlOption(
                new Link('Date', $this->get('router')->generate('search', $dateQuery->all())),
                SortControlOption::ASC === $order ? SortControlOption::ASC : SortControlOption::DESC
            ),
        ]);

        return new Response($this->get('templating')->render('::search.html.twig', $arguments));
    }
}
