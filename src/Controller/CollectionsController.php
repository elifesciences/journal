<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Collection;
use eLife\ApiSdk\Model\Model;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use eLife\Patterns\ViewModel\LeadPara;
use eLife\Patterns\ViewModel\LeadParas;
use eLife\Patterns\ViewModel\ListHeading;
use eLife\Patterns\ViewModel\ListingTeasers;
use eLife\Patterns\ViewModel\Teaser;
use Symfony\Component\HttpFoundation\Response;

final class CollectionsController extends Controller
{
    public function listAction() : Response
    {
        $page = 1;
        $perPage = 6;

        $arguments = $this->defaultPageArguments();

        $arguments['contentHeader'] = ContentHeaderNonArticle::basic('eLife collections');

        $arguments['latestCollectionsHeading'] = new ListHeading('Latest collections');
        $arguments['latestCollections'] = $this->get('elife.api_sdk.collections')
            ->slice(($page * $perPage) - $perPage, $perPage)
            ->then(function (Sequence $result) use ($arguments) {
                if ($result->isEmpty()) {
                    return null;
                }

                return ListingTeasers::basic(
                    $result->map(function (Collection $collection) {
                        return $this->get('elife.journal.view_model.converter')->convert($collection, Teaser::class);
                    })->toArray(),
                    $arguments['latestCollectionsHeading']['heading']
                );
            });

        return new Response($this->get('templating')->render('::collections.html.twig', $arguments));
    }

    public function collectionAction(string $id) : Response
    {
        $arguments = $this->defaultPageArguments();

        $arguments['collection'] = $this->get('elife.api_sdk.collections')->get($id);

        $arguments['contentHeader'] = $arguments['collection']
            ->then(function (Collection $collection) {
                return $this->get('elife.journal.view_model.converter')->convert($collection, ContentHeaderNonArticle::class);
            });

        $arguments['lead_paras'] = $arguments['collection']
            ->then(function (Collection $collection) {
                return new LeadParas([new LeadPara($collection->getImpactStatement())]);
            })
            ->otherwise(function () {
                return null;
            });

        $arguments['collectionList'] = $arguments['collection']
            ->then(function (Collection $collection) {
                return ListingTeasers::basic(
                    $collection->getContent()->map(function (Model $model) {
                        return $this->get('elife.journal.view_model.converter')->convert($model, Teaser::class);
                    })->toArray(),
                    'Collection'
                );
            });

        return new Response($this->get('templating')->render('::collection.html.twig', $arguments));
    }
}
