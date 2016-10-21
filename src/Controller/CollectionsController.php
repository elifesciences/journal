<?php

namespace eLife\Journal\Controller;

use DateTimeImmutable;
use eLife\ApiClient\ApiClient\CollectionsClient;
use eLife\ApiClient\Exception\BadResponse;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\Patterns\ViewModel\BackgroundImage;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use eLife\Patterns\ViewModel\Date;
use eLife\Patterns\ViewModel\LeadPara;
use eLife\Patterns\ViewModel\LeadParas;
use eLife\Patterns\ViewModel\Link;
use eLife\Patterns\ViewModel\ListHeading;
use eLife\Patterns\ViewModel\Meta;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

final class CollectionsController extends Controller
{
    public function listAction() : Response
    {
        $page = 1;
        $perPage = 6;

        $arguments = $this->defaultPageArguments();

        $arguments['contentHeader'] = ContentHeaderNonArticle::basic('eLife collections');

        $arguments['latestCollectionsHeading'] = new ListHeading('Latest collections');
        $arguments['latestCollections'] = $this->get('elife.api_client.collections')
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

    public function collectionAction(string $id) : Response
    {
        $arguments = $this->defaultPageArguments();

        $arguments['collection'] = $this->get('elife.api_client.collections')
            ->getCollection(['Accept' => new MediaType(CollectionsClient::TYPE_COLLECTION, 1)], $id)
            ->otherwise(function (Throwable $e) {
                if ($e instanceof BadResponse && 404 === $e->getResponse()->getStatusCode()) {
                    throw new NotFoundHttpException('Collection not found', $e);
                }
            });

        $arguments['contentHeader'] = $arguments['collection']
            ->then(function (Result $collection) {
                return ContentHeaderNonArticle::curatedContentListing($collection['title'], false,
                    $collection['subTitle'] ?? null,
                    null,
                    Meta::withLink(
                        new Link('Collection', $this->get('router')->generate('collections')),
                        new Date(DateTimeImmutable::createFromFormat(DATE_ATOM, $collection['updated']))
                    ),
                    null,
                    new BackgroundImage(
                        $collection['image']['banner']['sizes']['2:1'][900],
                        $collection['image']['banner']['sizes']['2:1'][1800]
                    )
                );
            });

        $arguments['lead_paras'] = $arguments['collection']
            ->then(function (Result $collection) {
                return new LeadParas([new LeadPara($collection['impactStatement'])]);
            })
            ->otherwise(function () {
                return null;
            });

        $arguments['collectionList'] = $arguments['collection']
            ->then(function (Result $collection) {
                return $this->get('elife.journal.view_model.factory.listing_teaser')
                    ->forItems($collection['content'], 'Collection');
            });

        return new Response($this->get('templating')->render('::collection.html.twig', $arguments));
    }
}
