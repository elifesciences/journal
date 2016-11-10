<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Event;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use eLife\Patterns\ViewModel\LeadPara;
use eLife\Patterns\ViewModel\LeadParas;
use eLife\Patterns\ViewModel\ListingTeasers;
use eLife\Patterns\ViewModel\Teaser;
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
            ->forType('open')
            ->reverse()
            ->slice(($page * $perPage) - $perPage, $perPage)
            ->then(function (Sequence $result) use ($arguments) {
                if ($result->isEmpty()) {
                    return null;
                }

                return ListingTeasers::basic(
                    $result->map(function (Event $event) {
                        return $this->get('elife.journal.view_model.converter')->convert($event, Teaser::class);
                    })->toArray(),
                    'Upcoming events'
                );
            });

        return new Response($this->get('templating')->render('::events.html.twig', $arguments));
    }

    public function eventAction(string $id) : Response
    {
        $arguments = $this->defaultPageArguments();

        $arguments['event'] = $this->get('elife.api_sdk.events')->get($id);

        $arguments['contentHeader'] = $arguments['event']
            ->then(function (Event $event) {
                return $this->get('elife.journal.view_model.converter')->convert($event, ContentHeaderNonArticle::class);
            });

        $arguments['leadParas'] = $arguments['event']
            ->then(function (Event $event) {
                return new LeadParas([new LeadPara($event->getImpactStatement())]);
            })
            ->otherwise(function () {
                return null;
            });

        $arguments['blocks'] = $arguments['event']
            ->then(function (Event $event) {
                return $event->getContent()->map(function (Block $block) {
                    return $this->get('elife.journal.view_model.converter')->convert($block);
                });
            });

        return new Response($this->get('templating')->render('::event.html.twig', $arguments));
    }
}
