<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Collection;
use eLife\Journal\Helper\Callback;
use eLife\Journal\Helper\Paginator;
use eLife\Journal\Pagerfanta\SequenceAdapter;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use eLife\Patterns\ViewModel\LeadParas;
use eLife\Patterns\ViewModel\ListingTeasers;
use eLife\Patterns\ViewModel\Teaser;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function GuzzleHttp\Promise\promise_for;

final class CollectionsController extends Controller
{
    use HasPages;

    public function listAction(Request $request) : Response
    {
        $page = (int) $request->query->get('page', 1);
        $perPage = 6;

        $arguments = $this->defaultPageArguments();

        $latestResearch = promise_for($this->get('elife.api_sdk.collections'))
            ->then(function (Sequence $sequence) use ($page, $perPage) {
                $pagerfanta = new Pagerfanta(new SequenceAdapter($sequence, $this->willConvertTo(Teaser::class)));
                $pagerfanta->setMaxPerPage($perPage)->setCurrentPage($page);

                return $pagerfanta;
            });

        $arguments['title'] = 'Collections';

        $arguments['paginator'] = $this->paginator(
            $latestResearch,
            $request,
            'Browse our collections',
            'collections'
        );

        $arguments['listing'] = $arguments['paginator']
            ->then($this->willConvertTo(ListingTeasers::class, ['type' => 'collections']));

        if (1 === $page) {
            return $this->createFirstPage($arguments);
        }

        return $this->createSubsequentPage($request, $arguments);
    }

    private function createFirstPage(array $arguments) : Response
    {
        $arguments['contentHeader'] = ContentHeaderNonArticle::basic('eLife collections');

        return new Response($this->get('templating')->render('::collections.html.twig', $arguments));
    }

    public function collectionAction(string $id) : Response
    {
        $collection = $this->get('elife.api_sdk.collections')
            ->get($id)
            ->otherwise($this->mightNotExist());

        $arguments = $this->defaultPageArguments($collection);

        $arguments['collection'] = $collection;

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
