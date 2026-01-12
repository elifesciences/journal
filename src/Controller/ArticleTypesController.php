<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Collection\Sequence;
use eLife\Journal\Helper\ModelName;
use eLife\Journal\Helper\Paginator;
use eLife\Journal\Pagerfanta\SequenceAdapter;
use eLife\Patterns\ViewModel\ContentHeader;
use eLife\Patterns\ViewModel\ListingTeasers;
use eLife\Patterns\ViewModel\Teaser;
use InvalidArgumentException;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use function GuzzleHttp\Promise\promise_for;

final class ArticleTypesController extends Controller
{
    public function listAction(Request $request, string $type) : Response
    {
        $page = (int) $request->query->get('page', 1);
        $perPage = 10;

        $arguments = $this->defaultPageArguments($request);

        try {
            $arguments['title'] = ModelName::plural($type);
        } catch (InvalidArgumentException $e) {
            throw new NotFoundHttpException('Unknown type '.$type, $e);
        }

        $latest = promise_for($this->get('elife.api_sdk.search')
            ->forType($type)
            ->sortBy('date'))
            ->then(function (Sequence $sequence) use ($page, $perPage) {
                $pagerfanta = new Pagerfanta(new SequenceAdapter($sequence, $this->willConvertTo(Teaser::class)));
                $pagerfanta->setMaxPerPage($perPage)->setCurrentPage($page);

                return $pagerfanta;
            });

        $arguments['paginator'] = $latest
            ->then(function (Pagerfanta $pagerfanta) use ($request, $type) {
                return new Paginator(
                    'Browse our '.ModelName::plural($type),
                    $pagerfanta,
                    function (int $page = null) use ($request) {
                        $routeParams = $request->attributes->get('_route_params');
                        $routeParams['page'] = $page;

                        return $this->get('router')->generate('article-type', $routeParams);
                    }
                );
            });

        $arguments['listing'] = $arguments['paginator']
            ->then($this->willConvertTo(ListingTeasers::class, ['type' => 'articles']));

        if (1 === $page) {
            return $this->createFirstPage($arguments, $type);
        }

        return $this->createSubsequentPage($request, $arguments);
    }

    private function createFirstPage(array $arguments, string $type) : Response
    {
        static $impactStatements = [
            'editorial' => 'Editorials are written by eLife editors or staff.',
            'feature' => 'eLife Feature Articles allow authors to discuss research culture, science policy and funding, careers in science and a variety of other topics related to science and research.',
            'insight' => 'Insight articles are related to original research papers in eLife and explain why the results reported in the paper are significant in a given field of research. Insight articles are commissioned by eLife staff.',
            'research-advance' => 'A Research Advance is a short article that allows either the authors of an eLife paper or other researchers to publish new results that build on the original research paper.',
            'research-article' => 'Research Articles published by eLife are full-length studies that present important breakthroughs across the life sciences and biomedicine. There is no maximum length and no limits on the number of display items.',
            'research-communication' => 'A Research Communication is an article that has been through an editorial process in which the authors decide how to respond to the <a href="https://doi.org/10.7554/eLife.36545">issues raised during peer review</a>.',
            'registered-report' => 'Registered Reports outline the proposed experimental designs and protocols, which are peer reviewed and published prior to data collection, as part of the Reproducibility Project: Cancer Biology, published by eLife.',
            'review-article' => 'Review Articles are intended to bring readers up-to-date with research on important topics. Review Articles are commissioned by Senior Editors.',
            'scientific-correspondence' => 'Scientific Correspondence allows authors to challenge the central findings of a published paper, and gives the original authors an opportunity to respond.',
            'short-report' => 'A Short Report allows authors to publish the results of a small number of experiments, provided the conclusion is clear and justified, and the findings are novel and judged to be of high importance.',
            'tools-resources' => 'A Tools and Resources article allows authors to publish the details of new experimental techniques, datasets, software tools, and other resources.',
        ];

        $arguments['contentHeader'] = new ContentHeader($arguments['title'], null, $impactStatements[$type] ?? null);

        return new Response($this->get('templating')->render('::article-type.html.twig', $arguments));
    }
}
