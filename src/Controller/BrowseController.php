<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Client\Search;
use eLife\ApiSdk\Collection\Sequence;
use eLife\Journal\Helper\Callback;
use eLife\Journal\Helper\Paginator;
use eLife\Journal\Pagerfanta\SequenceAdapter;
use eLife\Patterns\ViewModel\Button;
use eLife\Patterns\ViewModel\ContentHeader;
use eLife\Patterns\ViewModel\Filter;
use eLife\Patterns\ViewModel\FilterGroup;
use eLife\Patterns\ViewModel\FilterPanel;
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
use function GuzzleHttp\Promise\promise_for;

final class BrowseController extends Controller
{
    public function queryAction(Request $request) : Response
    {
        $page = (int) $request->query->get('page', 1);
        $perPage = 10;

        $arguments = $this->defaultPageArguments($request);
        
        $significance = $request->query->get('significance');
        $strength = $request->query->get('strength');

        $arguments['query'] = $query = [
            'significance' => $significance,
            'strength' => $strength,
            'include-original' => $request->query->getBoolean('include-original'),
            'subjects' => $request->query->get('subjects', []),
        ];

        $browse = $this->get('elife.api_sdk.browse.page')
            ->forSubject(...$query['subjects'])
            ->forType(...$this->researchTypes())
            ->forElifeAssessmentSignificance(...$this->significanceTermQuery($significance, $query['include-original']))
            ->forElifeAssessmentStrength(...$this->strengthTermQuery($strength, $query['include-original']))
            ->sortBy('date');

        $browse = promise_for($browse);

        $pagerfanta = $browse
            ->then(function (Sequence $sequence) use ($page, $perPage) {
                $pagerfanta = new Pagerfanta(new SequenceAdapter($sequence, $this->willConvertTo(Teaser::class)));
                $pagerfanta->setMaxPerPage($perPage)->setCurrentPage($page);

                return $pagerfanta;
            });

        $arguments['title'] = 'Browse the latest research';

        $arguments['paginator'] = $pagerfanta
            ->then(function (Pagerfanta $pagerfanta) use ($arguments, $request, $query) {
                return new Paginator(
                    $arguments['title'],
                    $pagerfanta,
                    function (int $page = null) use ($request, $query) {
                        $routeParams = $query + $request->attributes->get('_route_params');
                        $routeParams['page'] = $page;

                        return $this->get('router')->generate('browse', $routeParams);
                    }
                );
            });

        $arguments['listing'] = $arguments['paginator']
            ->then(Callback::methodEmptyOr('getTotal', $this->willConvertTo(ListingTeasers::class, ['heading' => ''])));

        if (1 === $page) {
            return $this->createFirstPage($browse, $arguments);
        }

        return $this->createSubsequentPage($request, $arguments);
    }


    private function createFirstPage(PromiseInterface $browse, array $arguments) : Response
    {
        $arguments['contentHeader'] = new ContentHeader(
            $arguments['title']
        );

        $arguments['messageBar'] = $arguments['paginator']
            ->then(function (Paginator $paginator) {
                if (1 === $paginator->getTotal()) {
                    return new MessageBar('1 article found');
                }

                return new MessageBar('<b>Showing '.number_format($paginator->getTotal()).' '.(1 === $paginator->getTotal() ? 'article' : 'articles').'</b>');
            });

        $arguments['sortControl'] = new SortControl([
            new SortControlOption(
                new Link('Sorted by Publication date', $this->get('router')->generate('browse', $arguments['query']))
            ),
        ]);
        
        $prepareTermsFilter = function (array $terms, string $current = null) {
            return array_merge(
                [
                    new Filter(false, 'Show all', null, null, 'all'),
                ],
                array_map(
                    function ($t) use ($current) {
                        return new Filter($current === $t, $t, null, null, $t);
                    },
                    $terms
                )
            );
        };

        $arguments['filterPanel'] = $browse
            ->then(function (Search $browse) use ($arguments, $prepareTermsFilter) {
                $filterGroups = [
                    new FilterGroup(
                        'Significance (minimum)',
                        $prepareTermsFilter(
                            $this->significanceTerms(),
                            $arguments['query']['significance']
                        ),
                        'significance'
                    ),
                    new FilterGroup(
                        'Strength (minimum)',
                        $prepareTermsFilter(
                            $this->strengthTerms(),
                            $arguments['query']['strength']
                        ),
                        'strength'
                    ),
                    new FilterGroup(null, [
                        new Filter(
                            $arguments['query']['include-original'],
                            'Include papers accepted via eLife’s original publishing model',
                            null,
                            'include-original'
                        ),
                    ]),
                ];

                if (count($browse->subjects())) {
                    $subjectFilters = [];
                    foreach ($browse->subjects() as $subject => $results) {
                        $subjectFilters[] = new Filter(in_array($subject->getId(), $arguments['query']['subjects']), $subject->getName(), null, 'subjects[]', $subject->getId());
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

        return new Response($this->get('templating')->render('::browse.html.twig', $arguments));
    }

    private function researchTypes()
    {
        return [
            'research-advance',
            'research-article',
            'short-report',
            'tools-resources',
            'reviewed-preprint',
        ];
    }
    
    private function termQuery(array $possibleTerms, string $term = null, $includeOriginal)
    {
        $terms = [];
        
        if ((is_null($term) || !in_array($term, $possibleTerms)) && !$includeOriginal) {
            $terms = $possibleTerms;
            $terms[] = 'not-assigned';
        }
        
        if (!is_null($term) && in_array($term, $possibleTerms)) {
            foreach ($possibleTerms as $t) {
                $terms[] = $t;
                if ($t === $term) {
                    break;
                }
            }

            if ($includeOriginal) {
                $terms[] = 'not-applicable';
            }
        }
        
        return $terms;
    }

    private function significanceTermQuery(string $term = null, bool $includeOriginal)
    {
        return $this->termQuery($this->significanceTerms(), $term, $includeOriginal);
    }

    private function strengthTermQuery(string $term = null, bool $includeOriginal)
    {
        return $this->termQuery($this->strengthTerms(), $term, $includeOriginal);
    }
    
    private function significanceTerms()
    {
        return [
            'landmark',
            'fundamental',
            'important',
            'valuable',
            'useful',
        ];
    }
    
    private function strengthTerms()
    {
        return [
            'exceptional',
            'compelling',
            'convincing',
            'solid',
            'incomplete',
            'inadequate',
        ];
    }
}
