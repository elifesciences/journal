<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\LabsExperiment;
use eLife\Journal\Helper\Paginator;
use eLife\Journal\Pagerfanta\SequenceAdapter;
use eLife\Patterns\ViewModel\BackgroundImage;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use eLife\Patterns\ViewModel\ContentHeaderSimple;
use eLife\Patterns\ViewModel\GridListing;
use eLife\Patterns\ViewModel\LeadPara;
use eLife\Patterns\ViewModel\LeadParas;
use eLife\Patterns\ViewModel\Link;
use eLife\Patterns\ViewModel\LoadMore;
use eLife\Patterns\ViewModel\Pager;
use eLife\Patterns\ViewModel\Teaser;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function GuzzleHttp\Promise\all;
use function GuzzleHttp\Promise\promise_for;

final class LabsController extends Controller
{
    public function listAction(Request $request) : Response
    {
        $page = (int) $request->query->get('page', 1);
        $perPage = 6;

        $arguments = $this->defaultPageArguments();

        $experiments = promise_for($this->get('elife.api_sdk.labs_experiments'))
            ->then(function (Sequence $sequence) use ($page, $perPage) {
                $pagerfanta = new Pagerfanta(new SequenceAdapter($sequence));
                $pagerfanta->setMaxPerPage($perPage)->setCurrentPage($page);

                return $pagerfanta;
            });

        $arguments['paginator'] = $experiments
            ->then(function (Pagerfanta $pagerfanta) use ($request) {
                return new Paginator($pagerfanta, function (int $page = null) use ($request) {
                    $routeParams = $request->attributes->get('_route_params');
                    $routeParams['page'] = $page;

                    return $this->get('router')->generate('labs', $routeParams);
                });
            });

        $arguments['experiments'] = $experiments
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

        $arguments['experiments'] = all(['experiments' => $arguments['experiments'], 'paginator' => $arguments['paginator']])
            ->then(function (array $parts) {
                $experiments = $parts['experiments'];
                $paginator = $parts['paginator'];

                if ($experiments->isEmpty()) {
                    return null;
                }

                $teasers = $experiments->map(function (LabsExperiment $experiment) {
                    return $this->get('elife.journal.view_model.converter')->convert($experiment, Teaser::class, ['variant' => 'grid']);
                })->toArray();

                if ($paginator->getNextPage()) {
                    return GridListing::forTeasers(
                        $teasers,
                        'Experiments',
                        $paginator->getNextPage() ? new LoadMore(new Link('Load more experiments', $paginator->getNextPagePath())) : null
                    );
                }

                return GridListing::forTeasers($teasers, 'Experiments');
            });

        return new Response($this->get('templating')->render('::labs.html.twig', $arguments));
    }

    private function createSubsequentPage(array $arguments) : Response
    {
        $arguments['contentHeader'] = $arguments['paginator']
            ->then(function (Paginator $paginator) {
                return new ContentHeaderSimple(
                    'Browse our experiments',
                    sprintf('Page %s of %s', number_format($paginator->getCurrentPage()), number_format(count($paginator)))
                );
            });

        $arguments['experiments'] = all(['experiments' => $arguments['experiments'], 'paginator' => $arguments['paginator']])
            ->then(function (array $parts) {
                $experiments = $parts['experiments'];
                $paginator = $parts['paginator'];

                return GridListing::forTeasers(
                    $experiments->map(function (LabsExperiment $experiment) {
                        return $this->get('elife.journal.view_model.converter')->convert($experiment, Teaser::class, ['variant' => 'grid']);
                    })->toArray(),
                    null,
                    new Pager(
                        $paginator->getPreviousPage() ? new Link('Newer', $paginator->getPreviousPagePath()) : null,
                        $paginator->getNextPage() ? new Link('Older', $paginator->getNextPagePath()) : null
                    )
                );
            });

        return new Response($this->get('templating')->render('::labs-alt.html.twig', $arguments));
    }

    public function experimentAction(int $number) : Response
    {
        $arguments = $this->defaultPageArguments();

        $arguments['experiment'] = $this->get('elife.api_sdk.labs_experiments')->get($number);

        $arguments['contentHeader'] = $arguments['experiment']
            ->then(function (LabsExperiment $experiment) {
                return $this->get('elife.journal.view_model.converter')->convert($experiment, ContentHeaderNonArticle::class);
            });

        $arguments['leadParas'] = $arguments['experiment']
            ->then(function (LabsExperiment $experiment) {
                return new LeadParas([new LeadPara($experiment->getImpactStatement())]);
            })
            ->otherwise(function () {
                return null;
            });

        $arguments['blocks'] = $arguments['experiment']
            ->then(function (LabsExperiment $experiment) {
                return $experiment->getContent()->map(function (Block $block) {
                    return $this->get('elife.journal.view_model.converter')->convert($block);
                });
            });

        return new Response($this->get('templating')->render('::labs-experiment.html.twig', $arguments));
    }
}
