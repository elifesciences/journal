<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Collection\Sequence;
use eLife\Journal\Helper\Callback;
use eLife\Journal\Helper\Paginator;
use eLife\Journal\Pagerfanta\SequenceAdapter;
use eLife\Patterns\ViewModel\BackgroundImage;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use eLife\Patterns\ViewModel\GridListing;
use eLife\Patterns\ViewModel\LeadPara;
use eLife\Patterns\ViewModel\LeadParas;
use eLife\Patterns\ViewModel\Teaser;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function GuzzleHttp\Promise\promise_for;

final class LabsController extends Controller
{
    public function listAction(Request $request) : Response
    {
        $page = (int) $request->query->get('page', 1);
        $perPage = 8;

        $arguments = $this->defaultPageArguments();

        $experiments = promise_for($this->get('elife.api_sdk.labs_experiments'))
            ->then(function (Sequence $sequence) use ($page, $perPage) {
                $pagerfanta = new Pagerfanta(new SequenceAdapter($sequence, $this->willConvertTo(Teaser::class, ['variant' => 'grid'])));
                $pagerfanta->setMaxPerPage($perPage)->setCurrentPage($page);

                return $pagerfanta;
            });

        $arguments['title'] = 'Labs';

        $arguments['paginator'] = $experiments
            ->then(function (Pagerfanta $pagerfanta) use ($request) {
                return new Paginator(
                    'Browse our experiments',
                    $pagerfanta,
                    function (int $page = null) use ($request) {
                        $routeParams = $request->attributes->get('_route_params');
                        $routeParams['page'] = $page;

                        return $this->get('router')->generate('labs', $routeParams);
                    }
                );
            });

        $arguments['listing'] = $arguments['paginator']
            ->then($this->willConvertTo(GridListing::class, ['heading' => 'Experiments', 'type' => 'experiments']));

        if (1 === $page) {
            return $this->createFirstPage($arguments);
        }

        return $this->createSubsequentPage($request, $arguments);
    }

    private function createFirstPage(array $arguments) : Response
    {
        $arguments['contentHeader'] = ContentHeaderNonArticle::basic('eLife Labs', true, null, null, null,
            new BackgroundImage(
                $this->get('puli.url_generator')->generateUrl('/elife/journal/images/banners/labs-lo-res.jpg'),
                $this->get('puli.url_generator')->generateUrl('/elife/journal/images/banners/labs-hi-res.jpg')
            ));

        $arguments['leadParas'] = new LeadParas([
            new LeadPara('eLife Labs showcases experiments in new functionality and technologies. Some experiments may be
developed further to become features on the eLife platform.'),
            new LeadPara('Feedback welcome!'),
        ]);

        return new Response($this->get('templating')->render('::labs.html.twig', $arguments));
    }

    public function experimentAction(int $number) : Response
    {
        $experiment = $this->get('elife.api_sdk.labs_experiments')->get($number);

        $arguments = $this->defaultPageArguments($experiment);

        $arguments['experiment'] = $experiment;

        $arguments['contentHeader'] = $arguments['experiment']
            ->then($this->willConvertTo(ContentHeaderNonArticle::class));

        $arguments['leadParas'] = $arguments['experiment']
            ->then(Callback::methodEmptyOr('getImpactStatement', $this->willConvertTo(LeadParas::class)));

        $arguments['blocks'] = $arguments['experiment']
            ->then($this->willConvertContent());

        return new Response($this->get('templating')->render('::labs-experiment.html.twig', $arguments));
    }
}
