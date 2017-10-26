<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\JobAdvert;
use eLife\Journal\Helper\Callback;
use eLife\Journal\Helper\Paginator;
use eLife\Journal\Pagerfanta\SequenceAdapter;
use eLife\Patterns\ViewModel\ContentHeader;
use eLife\Patterns\ViewModel\ListingTeasers;
use eLife\Patterns\ViewModel\Teaser;
use Pagerfanta\Pagerfanta;
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

        $latest = promise_for($this->get('elife.api_sdk.job_adverts')
            ->show('open'))
            ->then(function (Sequence $sequence) use ($page, $perPage) {
                $pagerfanta = new Pagerfanta(new SequenceAdapter($sequence, $this->willConvertTo(Teaser::class)));
                $pagerfanta->setMaxPerPage($perPage)->setCurrentPage($page);

                return $pagerfanta;
            });

        $arguments['title'] = 'eLife Jobs';

        $arguments['paginator'] = $latest
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
            ->then($this->willConvertTo(ListingTeasers::class, ['heading' => 'Latest', 'type' => 'job-adverts', 'emptyText' => 'No vacancies at present.']));

        if (1 === $page) {
            return $this->createFirstPage($arguments);
        }

        return $this->createSubsequentPage($request, $arguments);
    }

    private function createFirstPage(array $arguments) : Response
    {
        $arguments['contentHeader'] = new ContentHeader(
          $arguments['title'],
          null
        );

        return new Response($this->get('templating')->render('::job-adverts.html.twig', $arguments));
    }

    public function jobAdvertAction(Request $request, string $id) : Response
    {
        $jobAdvert = $this->get('elife.api_sdk.job_advert')
            ->get($id)
            ->otherwise($this->mightNotExist())
            ->then(function (JobAdvert $jobAdvert) {
                return $jobAdvert;
            })
            ->then($this->checkSlug($request, Callback::method('getTitle')));

        $arguments = $this->defaultPageArguments($request, $jobAdvert);

        $arguments['title'] = $jobAdvert
            ->then(Callback::method('getTitle'));

        $arguments['job-advert'] = $jobAdvert;

        $arguments['contentHeader'] = $arguments['job-advert']
            ->then($this->willConvertTo(ContentHeader::class));

        $arguments['blocks'] = $arguments['job-advert']
            ->then($this->willConvertContent());

        return new Response($this->get('templating')->render('::job-advert.html.twig', $arguments));
    }
}
