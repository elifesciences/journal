<?php

namespace eLife\Journal\Controller;

use eLife\ApiClient\Result;
use eLife\ApiSdk\Client\Search;
use eLife\ApiSdk\Collection\Sequence;
use eLife\Journal\Helper\Callback;
use eLife\Journal\Helper\Paginator;
use eLife\Journal\Pagerfanta\SequenceAdapter;
use eLife\Patterns\ViewModel\ListingTeasers;
use eLife\Patterns\ViewModel\MessageBar;
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

        $arguments = $this->defaultPageArguments();

        $search = $this->get('elife.api_sdk.search')
            ->forQuery($for)
            ->forSubject(...$subjects)
            ->sortBy('date');

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
            return $this->createFirstPage($arguments);
        }

        return $this->createSubsequentPage($request, $arguments);
    }

    private function createFirstPage(array $arguments) : Response
    {
        $arguments['messageBar'] = $arguments['paginator']
            ->then(function (Paginator $paginator) {
                if (1 === $paginator->getTotal()) {
                    return new MessageBar('1 result found');
                }

                return new MessageBar(number_format($paginator->getTotal()).' results found');
            });

        return new Response($this->get('templating')->render('::search.html.twig', $arguments));
    }
}
