<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Collection;
use eLife\Journal\Helper\Callback;
use eLife\Journal\Helper\Paginator;
use eLife\Journal\Pagerfanta\SequenceAdapter;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use eLife\Patterns\ViewModel\ContentHeaderSimple;
use eLife\Patterns\ViewModel\LeadParas;
use eLife\Patterns\ViewModel\Link;
use eLife\Patterns\ViewModel\ListHeading;
use eLife\Patterns\ViewModel\ListingTeasers;
use eLife\Patterns\ViewModel\LoadMore;
use eLife\Patterns\ViewModel\Pager;
use eLife\Patterns\ViewModel\Teaser;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function GuzzleHttp\Promise\all;
use function GuzzleHttp\Promise\promise_for;

final class CollectionsController extends Controller
{
    public function listAction(Request $request) : Response
    {
        $page = (int) $request->query->get('page', 1);
        $perPage = 6;

        $arguments = $this->defaultPageArguments();

        $latestResearch = promise_for($this->get('elife.api_sdk.collections'))
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

                    return $this->get('router')->generate('collections', $routeParams);
                });
            });

        $arguments['latestCollections'] = $latestResearch
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
        $arguments['contentHeader'] = ContentHeaderNonArticle::basic('eLife collections');

        $arguments['latestCollectionsHeading'] = new ListHeading($latestCollectionsHeading = 'Latest collections');
        $arguments['latestCollections'] = all(['latestCollections' => $arguments['latestCollections'], 'paginator' => $arguments['paginator']])
            ->then(function (array $parts) use ($latestCollectionsHeading) {
                $latestCollections = $parts['latestCollections'];
                $paginator = $parts['paginator'];

                if ($latestCollections->isEmpty()) {
                    return null;
                }

                $teasers = $latestCollections->map($this->willConvertTo(Teaser::class))->toArray();

                if ($paginator->getNextPage()) {
                    return ListingTeasers::withPagination(
                        $teasers,
                        $paginator->getNextPage() ? new LoadMore(new Link('Load more', $paginator->getNextPagePath())) : null,
                        $latestCollectionsHeading
                    );
                }

                return ListingTeasers::basic($teasers, $latestCollectionsHeading);
            });

        return new Response($this->get('templating')->render('::collections.html.twig', $arguments));
    }

    private function createSubsequentPage(array $arguments) : Response
    {
        $arguments['contentHeader'] = $arguments['paginator']
            ->then(function (Paginator $paginator) {
                return new ContentHeaderSimple(
                    'Browse our collections',
                    sprintf('Page %s of %s', number_format($paginator->getCurrentPage()), number_format(count($paginator)))
                );
            });

        $arguments['latestCollections'] = all(['latestCollections' => $arguments['latestCollections'], 'paginator' => $arguments['paginator']])
            ->then(function (array $parts) {
                $latestCollections = $parts['latestCollections'];
                $paginator = $parts['paginator'];

                return ListingTeasers::withPagination(
                    $latestCollections->map($this->willConvertTo(Teaser::class))->toArray(),
                    new Pager(
                        $paginator->getPreviousPage() ? new Link('Newer', $paginator->getPreviousPagePath()) : null,
                        $paginator->getNextPage() ? new Link('Older', $paginator->getNextPagePath()) : null
                    )
                );
            });

        return new Response($this->get('templating')->render('::collections-alt.html.twig', $arguments));
    }

    public function collectionAction(string $id) : Response
    {
        $arguments = $this->defaultPageArguments();

        $arguments['collection'] = $this->get('elife.api_sdk.collections')->get($id);

        $arguments['contentHeader'] = $arguments['collection']
            ->then($this->willConvertTo(ContentHeaderNonArticle::class));

        $arguments['lead_paras'] = $arguments['collection']
            ->then(Callback::methodEmptyOr('getImpactStatement', $this->willConvertTo(LeadParas::class)));

        $arguments['collectionList'] = $arguments['collection']
            ->then(function (Collection $collection) {
                return ListingTeasers::basic(
                    $collection->getContent()->map($this->willConvertTo(Teaser::class))->toArray(),
                    'Collection'
                );
            });

        return new Response($this->get('templating')->render('::collection.html.twig', $arguments));
    }
}
