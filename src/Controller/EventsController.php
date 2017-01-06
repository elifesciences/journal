<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Event;
use eLife\Journal\Helper\Callback;
use eLife\Journal\Helper\Paginator;
use eLife\Journal\Pagerfanta\SequenceAdapter;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use eLife\Patterns\ViewModel\ContentHeaderSimple;
use eLife\Patterns\ViewModel\LeadParas;
use eLife\Patterns\ViewModel\Link;
use eLife\Patterns\ViewModel\ListingTeasers;
use eLife\Patterns\ViewModel\Pager;
use eLife\Patterns\ViewModel\Teaser;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function GuzzleHttp\Promise\all;
use function GuzzleHttp\Promise\promise_for;

final class EventsController extends Controller
{
    public function listAction(Request $request) : Response
    {
        $page = (int) $request->query->get('page', 1);
        $perPage = 6;

        $arguments = $this->defaultPageArguments();

        $upcomingEvents = promise_for($this->get('elife.api_sdk.events')
            ->forType('open')
            ->reverse())
            ->then(function (Sequence $sequence) use ($page, $perPage) {
                $pagerfanta = new Pagerfanta(new SequenceAdapter($sequence));
                $pagerfanta->setMaxPerPage($perPage)->setCurrentPage($page);

                return $pagerfanta;
            });

        $arguments['paginator'] = $upcomingEvents
            ->then(function (Pagerfanta $pagerfanta) use ($request) {
                return new Paginator($pagerfanta, function (int $page = null) use ($request) {
                    $routeParams = $request->attributes->get('_route_params');
                    $routeParams['page'] = $page;

                    return $this->get('router')->generate('events', $routeParams);
                });
            });

        $arguments['upcomingEvents'] = $upcomingEvents
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
        $arguments['contentHeader'] = ContentHeaderNonArticle::basic('eLife events');

        $arguments['upcomingEvents'] = all(['upcomingEvents' => $arguments['upcomingEvents'], 'paginator' => $arguments['paginator']])
            ->then(function (array $parts) {
                $upcomingEvents = $parts['upcomingEvents'];
                $paginator = $parts['paginator'];

                if ($upcomingEvents->isEmpty()) {
                    return null;
                }

                $teasers = $upcomingEvents->map($this->willConvertTo(Teaser::class))->toArray();

                if ($paginator->getNextPage()) {
                    return ListingTeasers::withPagination(
                        $teasers,
                        $paginator->getNextPage() ? Pager::firstPage(new Link('Load more events', $paginator->getNextPagePath())) : null,
                        'Upcoming events'
                    );
                }

                return ListingTeasers::basic($teasers, 'Upcoming events');
            });

        return new Response($this->get('templating')->render('::events.html.twig', $arguments));
    }

    private function createSubsequentPage(array $arguments) : Response
    {
        $arguments['contentHeader'] = $arguments['paginator']
            ->then(function (Paginator $paginator) {
                return new ContentHeaderSimple(
                    'Browse our upcoming events',
                    sprintf('Page %s of %s', number_format($paginator->getCurrentPage()), number_format(count($paginator)))
                );
            });

        $arguments['upcomingEvents'] = all(['upcomingEvents' => $arguments['upcomingEvents'], 'paginator' => $arguments['paginator']])
            ->then(function (array $parts) {
                $upcomingEvents = $parts['upcomingEvents'];
                $paginator = $parts['paginator'];

                return ListingTeasers::withPagination(
                    $upcomingEvents->map($this->willConvertTo(Teaser::class))->toArray(),
                    Pager::subsequentPage(
                        $paginator->getPreviousPage() ? new Link('More recent events', $paginator->getPreviousPagePath()) : null,
                        $paginator->getNextPage() ? new Link('Less recent events', $paginator->getNextPagePath()) : null
                    )
                );
            });

        return new Response($this->get('templating')->render('::events-alt.html.twig', $arguments));
    }

    public function eventAction(string $id) : Response
    {
        $arguments = $this->defaultPageArguments();

        $arguments['event'] = $this->get('elife.api_sdk.events')->get($id);

        $arguments['contentHeader'] = $arguments['event']
            ->then($this->willConvertTo(ContentHeaderNonArticle::class));

        $arguments['leadParas'] = $arguments['event']
            ->then(Callback::methodEmptyOr('getImpactStatement', $this->willConvertTo(LeadParas::class)));

        $arguments['blocks'] = $arguments['event']
            ->then($this->willConvertContent());

        return new Response($this->get('templating')->render('::event.html.twig', $arguments));
    }
}
