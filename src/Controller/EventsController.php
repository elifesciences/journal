<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\ApiClient\EventsClient;
use eLife\ApiSdk\MediaType;
use eLife\ApiSdk\Result;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use Symfony\Component\HttpFoundation\Response;

final class EventsController extends Controller
{
    public function listAction() : Response
    {
        $page = 1;
        $perPage = 6;

        $arguments = $this->defaultPageArguments();

        $arguments['contentHeader'] = ContentHeaderNonArticle::basic('eLife events');

        $arguments['upcomingEvents'] = $this->get('elife.api_sdk.events')
            ->listEvents(['Accept' => new MediaType(EventsClient::TYPE_EVENT_LIST, 1)], $page, $perPage, 'open', false)
            ->then(function (Result $result) use ($arguments) {
                if (empty($result['items'])) {
                    return null;
                }

                $items = array_map(function (array $item) {
                    $item['type'] = 'event';

                    return $item;
                }, $result['items']);

                return $this->get('elife.journal.view_model.factory.listing_teaser')
                    ->forItems($items, 'Upcoming events');
            });

        return new Response($this->get('templating')->render('::events.html.twig', $arguments));
    }
}
