<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\ApiClient\CollectionsClient;
use eLife\ApiSdk\MediaType;
use eLife\ApiSdk\Result;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use eLife\Patterns\ViewModel\ListHeading;
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
            ->listCollections(['Accept' => new MediaType(CollectionsClient::TYPE_COLLECTION_LIST, 1)], $page, $perPage)
            ->then(function (Result $result) use ($arguments) {
                if (empty($result['items'])) {
                    return null;
                }

                $items = array_map(function (array $item) {
                    $item['type'] = 'collection';

                    return $item;
                }, $result['items']);

                return $this->get('elife.journal.view_model.factory.listing_teaser')
                    ->forItems($items, $arguments['latestCollectionsHeading']['heading']);
            });

        return new Response($this->get('templating')->render('::collections.html.twig', $arguments));
    }
}
