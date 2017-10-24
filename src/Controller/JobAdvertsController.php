<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\JobAdvert;
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

final class JobAdvertsController extends Controller
{
    public function listAction(Request $request) : Response
    {
        $page = (int) $request->query->get('page', 1);
        $perPage = 10;

        $arguments = $this->defaultPageArguments($request);

        $upcomingEvents = promise_for($this->get('elife.api_sdk.job_adverts')
            ->show('open')
            ->reverse())
            ->then(function (Sequence $sequence) use ($page, $perPage) {
                $pagerfanta = new Pagerfanta(new SequenceAdapter($sequence, $this->willConvertTo(Teaser::class)));
                $pagerfanta->setMaxPerPage($perPage)->setCurrentPage($page);

                return $pagerfanta;
            });

        $arguments['title'] = 'Job adverts';

        $arguments['paginator'] = $upcomingEvents
            ->then(function (Pagerfanta $pagerfanta) use ($request) {
                return new Paginator(
                    'Browse our open job adverts',
                    $pagerfanta,
                    function (int $page = null) use ($request) {
                        $routeParams = $request->attributes->get('_route_params');
                        $routeParams['page'] = $page;

                        return $this->get('router')->generate('job-adverts', $routeParams);
                    }
                );
            });

        $arguments['listing'] = $arguments['paginator']
            ->then($this->willConvertTo(ListingTeasers::class, ['heading' => 'Open job adverts', 'type' => 'job-adverts', 'emptyText' => 'There are currently no open job adverts. Please call back soon.']));

        if (1 === $page) {
            return $this->createFirstPage($arguments);
        }

        return $this->createSubsequentPage($request, $arguments);
    }

    private function createFirstPage(array $arguments) : Response
    {
        $arguments['contentHeader'] = new ContentHeader('eLife job adverts');

        return new Response($this->get('templating')->render('::job-adverts.html.twig', $arguments));
    }

    public function jobAdvertAction(Request $request, string $id) : Response
    {
        $jobAdvert = $this->get('elife.api_sdk.job-advert')
            ->get($id)
            ->otherwise($this->mightNotExist())
            ->then(function (JobAdvert $jobAdvert) {
                return $jobAdvert;
            })
            ->then($this->checkSlug($request, Callback::method('getTitle')));

        $arguments = $this->defaultPageArguments($request, $jobAdvert);

        $arguments['title'] = $jobAdvert
            ->then(Callback::method('getTitle'));

        $arguments['jobAdvert'] = $jobAdvert;

        $arguments['contentHeader'] = $arguments['jobAdvert']
            ->then($this->willConvertTo(ContentHeader::class));

        $arguments['blocks'] = $arguments['jobAdvert']
            ->then($this->willConvertContent());

        return new Response($this->get('templating')->render('::job-advert.html.twig', $arguments));
    }
}
