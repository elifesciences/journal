<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Client\Search;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\SearchTypes;
use eLife\Journal\Helper\Callback;
use eLife\Journal\Helper\Paginator;
use eLife\Journal\Pagerfanta\SequenceAdapter;
use eLife\Patterns\ViewModel\Button;
use eLife\Patterns\ViewModel\CompactForm;
use eLife\Patterns\ViewModel\Filter;
use eLife\Patterns\ViewModel\FilterGroup;
use eLife\Patterns\ViewModel\FilterPanel;
use eLife\Patterns\ViewModel\Form;
use eLife\Patterns\ViewModel\Input;
use eLife\Patterns\ViewModel\Link;
use eLife\Patterns\ViewModel\ListingTeasers;
use eLife\Patterns\ViewModel\MessageBar;
use eLife\Patterns\ViewModel\SearchBox;
use eLife\Patterns\ViewModel\SortControl;
use eLife\Patterns\ViewModel\SortControlOption;
use eLife\Patterns\ViewModel\Teaser;
use GuzzleHttp\Promise\PromiseInterface;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function GuzzleHttp\Promise\promise_for;

final class SearchController extends Controller
{
    public function queryAction(Request $request) : Response
    {
        $page = (int) $request->query->get('page', 1);
        $perPage = 10;
        // Sanitise the 'for' query parameter.
        $for = preg_replace(['/^\s+/', '/\s+$/', '/[\-\s]+/'], ['', '', ' '], $request->query->get('for'));

        $arguments = $this->defaultPageArguments($request);

        $arguments['query'] = $query = [
            'for' => $for,
            'subjects' => $request->query->get('subjects', []),
            'types' => $request->query->get('types', []),
            'sort' => $request->query->get('sort', 'relevance'),
            'order' => $request->query->get('order', SortControlOption::DESC),
        ];

        $apiTypes = [];
        if (in_array('magazine', $arguments['query']['types'])) {
            $apiTypes = array_merge($apiTypes, $this->magazineTypes());
        }
        if (in_array('research', $arguments['query']['types'])) {
            $apiTypes = array_merge($apiTypes, $this->researchTypes());
        }

        $search = $this->get('elife.api_sdk.search.slow')
            ->forQuery($arguments['query']['for'])
            ->forSubject(...$arguments['query']['subjects'])
            ->forType(...$apiTypes)
            ->sortBy($arguments['query']['sort']);

        if (SortControlOption::ASC === $arguments['query']['order']) {
            $search = $search->reverse();
        }

        $search = promise_for($search);

        $pagerfanta = $search
            ->then(function (Sequence $sequence) use ($page, $perPage) {
                $pagerfanta = new Pagerfanta(new SequenceAdapter($sequence, $this->willConvertTo(Teaser::class)));
                $pagerfanta->setMaxPerPage($perPage)->setCurrentPage($page);

                return $pagerfanta;
            });

        $arguments['title'] = 'Search';

        $arguments['searchBox'] = new SearchBox(
            new CompactForm(
                new Form($this->get('router')->generate('search'), 'search', 'GET'),
                new Input('Search by keyword or author', 'search', 'for', $arguments['query']['for'], 'Search by keyword or author'),
                'Search'
            )
        );

        $arguments['paginator'] = $pagerfanta
            ->then(function (Pagerfanta $pagerfanta) use ($request, $query) {
                return new Paginator(
                    'Browse the search results',
                    $pagerfanta,
                    function (int $page = null) use ($request, $query) {
                        $routeParams = $query + $request->attributes->get('_route_params');
                        $routeParams['page'] = $page;

                        return $this->get('router')->generate('search', $routeParams);
                    }
                );
            });

        $arguments['listing'] = $arguments['paginator']
            ->then(Callback::methodEmptyOr('getTotal', $this->willConvertTo(ListingTeasers::class, ['heading' => ''])));

        if (1 === $page) {
            return $this->createFirstPage($search, $arguments);
        }

        return $this->createSubsequentPage($request, $arguments);
    }

    private function createFirstPage(PromiseInterface $search, array $arguments) : Response
    {
        $arguments['messageBar'] = $arguments['paginator']
            ->then(function (Paginator $paginator) {
                if (1 === $paginator->getTotal()) {
                    return new MessageBar('1 result found');
                }

                return new MessageBar('<b>'.number_format($paginator->getTotal()).'</b> results found');
            });

        $currentOrder = SortControlOption::ASC === $arguments['query']['order'] ? SortControlOption::ASC : SortControlOption::DESC;
        $inverseOrder = SortControlOption::ASC === $arguments['query']['order'] ? SortControlOption::DESC : SortControlOption::ASC;

        $relevanceQuery = array_merge(
            $arguments['query'],
            [
                'sort' => 'relevance',
                'order' => 'relevance' === $arguments['query']['sort'] ? $inverseOrder : SortControlOption::DESC,
            ]
        );

        $dateQuery = array_merge(
            $arguments['query'],
            [
                'sort' => 'date',
                'order' => 'date' === $arguments['query']['sort'] ? $inverseOrder : SortControlOption::DESC,
            ]
        );

        $arguments['sortControl'] = new SortControl([
            new SortControlOption(
                new Link('Relevance', $this->get('router')->generate('search', $relevanceQuery)),
                'relevance' === $arguments['query']['sort'] ? $currentOrder : null
            ),
            new SortControlOption(
                new Link('Date', $this->get('router')->generate('search', $dateQuery)),
                'date' === $arguments['query']['sort'] ? $currentOrder : null
            ),
        ]);

        $arguments['filterPanel'] = $search
            ->then(function (Search $search) use ($arguments) {
                $filterGroups = [];

                $allTypes = $search->types();

                $filterGroups[] = new FilterGroup(
                    'Type',
                    [
                        new Filter(in_array('magazine', $arguments['query']['types']), 'Magazine', $this->countForTypes($this->magazineTypes(), $allTypes), 'types[]', 'magazine'),
                        new Filter(in_array('research', $arguments['query']['types']), 'Research', $this->countForTypes($this->researchTypes(), $allTypes), 'types[]', 'research'),
                    ]
                );

                if (count($search->subjects())) {
                    $subjectFilters = [];
                    foreach ($search->subjects() as $subject => $results) {
                        $subjectFilters[] = new Filter(in_array($subject->getId(), $arguments['query']['subjects']), $subject->getName(), $results, 'subjects[]', $subject->getId());
                    }

                    usort($subjectFilters, function (Filter $a, Filter $b) {
                        return $a['label'] <=> $b['label'];
                    });

                    $filterGroups[] = new FilterGroup('Research categories', $subjectFilters);
                }

                return new FilterPanel(
                    'Refine your results by:',
                    $filterGroups,
                    Button::form('Refine results', Button::TYPE_SUBMIT)
                );
            });

        return new Response($this->get('templating')->render('::search.html.twig', $arguments));
    }

    private function countForTypes(array $types, SearchTypes $allTypes) : int
    {
        return array_sum(array_filter(iterator_to_array($allTypes), function (int $count, string $key) use ($types) {
            return in_array($key, $types);
        }, ARRAY_FILTER_USE_BOTH));
    }

    private function magazineTypes()
    {
        return [
            'blog-article',
            'collection',
            'editorial',
            'feature',
            'insight',
            'interview',
            'labs-post',
            'podcast-episode',
        ];
    }

    private function researchTypes()
    {
        $types = [
            'correction',
            'registered-report',
            'replication-study',
            'research-advance',
            'research-article',
            'research-communication',
            'retraction',
            'review-article',
            'scientific-correspondence',
            'short-report',
            'tools-resources',
            'reviewed-preprint',
        ];

        return $types;
    }
}
