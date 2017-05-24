<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Event;
use eLife\Journal\Exception\EarlyResponse;
use eLife\Journal\Helper\Callback;
use eLife\Journal\Helper\Paginator;
use eLife\Journal\Pagerfanta\SequenceAdapter;
use eLife\Patterns\ViewModel\ContentHeader;
use eLife\Patterns\ViewModel\ListingTeasers;
use eLife\Patterns\ViewModel\Teaser;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function GuzzleHttp\Promise\promise_for;

final class EventsController extends Controller
{
    public function listAction(Request $request) : Response
    {
        $page = (int) $request->query->get('page', 1);
        $perPage = 6;

        $arguments = $this->defaultPageArguments($request);

        $upcomingEvents = promise_for($this->get('elife.api_sdk.events')
            ->forType('open')
            ->reverse())
            ->then(function (Sequence $sequence) use ($page, $perPage) {
                $pagerfanta = new Pagerfanta(new SequenceAdapter($sequence, $this->willConvertTo(Teaser::class)));
                $pagerfanta->setMaxPerPage($perPage)->setCurrentPage($page);

                return $pagerfanta;
            });

        $arguments['title'] = 'Events';

        $arguments['paginator'] = $upcomingEvents
            ->then(function (Pagerfanta $pagerfanta) use ($request) {
                return new Paginator(
                    'Browse our upcoming events',
                    $pagerfanta,
                    function (int $page = null) use ($request) {
                        $routeParams = $request->attributes->get('_route_params');
                        $routeParams['page'] = $page;

                        return $this->get('router')->generate('events', $routeParams);
                    }
                );
            });

        $arguments['listing'] = $arguments['paginator']
            ->then($this->willConvertTo(ListingTeasers::class, ['heading' => 'Upcoming events', 'type' => 'events', 'emptyText' => 'There are currently no pending events. Please call back soon.']));

        if (1 === $page) {
            return $this->createFirstPage($arguments);
        }

        return $this->createSubsequentPage($request, $arguments);
    }

    private function createFirstPage(array $arguments) : Response
    {
        $arguments['contentHeader'] = new ContentHeader('eLife events');

        return new Response($this->get('templating')->render('::events.html.twig', $arguments));
    }

    public function eventAction(Request $request, string $id) : Response
    {
        $event = $this->get('elife.api_sdk.events')
            ->get($id)
            ->otherwise($this->mightNotExist())
            ->then(function (Event $event) {
                if ($event->getUri()) {
                    throw new EarlyResponse(new RedirectResponse($event->getUri()));
                }

                return $event;
            })
            ->then($this->checkSlug($request, Callback::method('getTitle')));

        $arguments = $this->defaultPageArguments($request, $event);

        $arguments['title'] = $event
            ->then(Callback::method('getTitle'));

        $arguments['event'] = $event;

        $arguments['contentHeader'] = $arguments['event']
            ->then($this->willConvertTo(ContentHeader::class));

        $arguments['blocks'] = $arguments['event']
            ->then($this->willConvertContent());

        return new Response($this->get('templating')->render('::event.html.twig', $arguments));
    }
}
