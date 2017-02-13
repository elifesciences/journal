<?php

namespace eLife\Journal\Controller;

use eLife\ApiClient\Result;
use eLife\ApiSdk\Client\Search;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\SearchTypes;
use eLife\ApiSdk\Model\Subject;
use eLife\Journal\Helper\Callback;
use eLife\Journal\Helper\Paginator;
use eLife\Journal\Pagerfanta\SequenceAdapter;
use eLife\Patterns\ViewModel\Button;
use eLife\Patterns\ViewModel\Filter;
use eLife\Patterns\ViewModel\FilterGroup;
use eLife\Patterns\ViewModel\FilterPanel;
use eLife\Patterns\ViewModel\Form;
use eLife\Patterns\ViewModel\Link;
use eLife\Patterns\ViewModel\ListingTeasers;
use eLife\Patterns\ViewModel\MessageBar;
use eLife\Patterns\ViewModel\SortControl;
use eLife\Patterns\ViewModel\SortControlOption;
use eLife\Patterns\ViewModel\Teaser;
use GuzzleHttp\Promise\PromiseInterface;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function GuzzleHttp\Promise\all;
use function GuzzleHttp\Promise\promise_for;

final class SearchController extends Controller
{
    private static $magazineTypes = [
        'blog-article',
        'collection',
        'editorial',
        'feature',
        'insight',
        'interview',
        'labs-experiment',
        'podcast-episode',
    ];

    private static $researchTypes = [
        'correction',
        'registered-report',
        'replication-study',
        'research-advance',
        'research-article',
        'research-exchange',
        'retraction',
        'short-report',
        'tools-resources',
    ];

    public function queryAction(Request $request) : Response
    {
        $page = (int) $request->query->get('page', 1);
        $perPage = 6;

        $arguments = $this->defaultPageArguments();

        $arguments['query'] = [
            'for' => trim($request->query->get('for')),
            'subjects' => $request->query->get('subjects', []),
            'types' => $request->query->get('types', []),
            'sort' => $request->query->get('sort', 'relevance'),
            'order' => $request->query->get('order', SortControlOption::DESC),
        ];

        $apiTypes = [];
        if (in_array('magazine', $arguments['query']['types'])) {
            $apiTypes = array_merge($apiTypes, self::$magazineTypes);
        }
        if (in_array('research', $arguments['query']['types'])) {
            $apiTypes = array_merge($apiTypes, self::$researchTypes);
        }

        $search = $this->get('elife.api_sdk.search')
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

        $arguments['paginator'] = $pagerfanta
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

                return new MessageBar(number_format($paginator->getTotal()).' results found');
            });

        $relevanceQuery = array_merge(
            $arguments['query'],
            [
                'sort' => 'relevance',
                'order' => SortControlOption::ASC === $arguments['query']['order'] ? SortControlOption::DESC : SortControlOption::ASC,
            ]
        );

        $dateQuery = array_merge(
            $arguments['query'],
            [
                'sort' => 'date',
                'order' => SortControlOption::ASC === $arguments['query']['order'] ? SortControlOption::DESC : SortControlOption::ASC,
            ]
        );

        $arguments['sortControl'] = new SortControl([
            new SortControlOption(
                new Link('Relevance', $this->get('router')->generate('search', $relevanceQuery)),
                SortControlOption::ASC === $arguments['query']['order'] ? SortControlOption::ASC : SortControlOption::DESC
            ),
            new SortControlOption(
                new Link('Date', $this->get('router')->generate('search', $dateQuery)),
                SortControlOption::ASC === $arguments['query']['order'] ? SortControlOption::ASC : SortControlOption::DESC
            ),
        ]);

        $arguments['filterPanel'] = $search
            ->then(function (Search $search) use ($arguments) {
                $filterGroups = [];

                if (count($search->subjects())) {
                    $subjectFilters = [];
                    foreach ($search->subjects() as $subject => $results) {
                        $subjectFilters[] = new Filter(in_array($subject->getId(), $arguments['query']['subjects']), $subject->getName(), $results, 'subjects[]', $subject->getId());
                    }

                    usort($subjectFilters, function (Filter $a, Filter $b) {
                        return $a['label'] <=> $b['label'];
                    });

                    $filterGroups[] = new FilterGroup('Subject', $subjectFilters);
                }

                $allTypes = $search->types();

                $filterGroups[] = new FilterGroup(
                    'Type',
                    [
                        new Filter(in_array('magazine', $arguments['query']['types']), 'Magazine', $this->countForTypes(self::$magazineTypes, $allTypes), 'types[]', 'magazine'),
                        new Filter(in_array('research', $arguments['query']['types']), 'Research', $this->countForTypes(self::$researchTypes, $allTypes), 'types[]', 'research'),
                    ]
                );

                return new FilterPanel(
                    'Refine your results by:',
                    $filterGroups,
                    Button::form('Refine results', Button::TYPE_SUBMIT, null, Button::SIZE_SMALL)
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
}
