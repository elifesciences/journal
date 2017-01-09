<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Collection\Sequence;
use eLife\Journal\Helper\Callback;
use eLife\Journal\Helper\Paginator;
use eLife\Journal\Pagerfanta\SequenceAdapter;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use eLife\Patterns\ViewModel\ContentHeaderSimple;
use eLife\Patterns\ViewModel\LeadParas;
use eLife\Patterns\ViewModel\ListingTeasers;
use eLife\Patterns\ViewModel\Teaser;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function GuzzleHttp\Promise\promise_for;

final class InsideElifeController extends Controller
{
    public function listAction(Request $request) : Response
    {
        $page = (int) $request->query->get('page', 1);
        $perPage = 6;

        $arguments = $this->defaultPageArguments();

        $latest = promise_for($this->get('elife.api_sdk.blog_articles'))
            ->then(function (Sequence $sequence) use ($page, $perPage) {
                $pagerfanta = new Pagerfanta(new SequenceAdapter($sequence, $this->willConvertTo(Teaser::class)));
                $pagerfanta->setMaxPerPage($perPage)->setCurrentPage($page);

                return $pagerfanta;
            });

        $arguments['title'] = 'Inside eLife';

        $arguments['paginator'] = $latest
            ->then(function (Pagerfanta $pagerfanta) use ($request) {
                return new Paginator($pagerfanta, function (int $page = null) use ($request) {
                    $routeParams = $request->attributes->get('_route_params');
                    $routeParams['page'] = $page;

                    return $this->get('router')->generate('inside-elife', $routeParams);
                });
            });

        $arguments['listing'] = $arguments['paginator']
            ->then($this->willConvertTo(ListingTeasers::class, ['heading' => 'Latest', 'type' => 'articles']));

        if (1 === $page) {
            return $this->createFirstPage($arguments);
        }

        return $this->createSubsequentPage($arguments);
    }

    private function createFirstPage(array $arguments) : Response
    {
        $arguments['contentHeader'] = ContentHeaderNonArticle::basic('Inside eLife');

        return new Response($this->get('templating')->render('::inside-elife.html.twig', $arguments));
    }

    private function createSubsequentPage(array $arguments) : Response
    {
        $arguments['contentHeader'] = $arguments['paginator']
            ->then(function (Paginator $paginator) {
                return new ContentHeaderSimple(
                    'Browse Inside eLife',
                    sprintf('Page %s of %s', number_format($paginator->getCurrentPage()), number_format(count($paginator)))
                );
            });

        return new Response($this->get('templating')->render('::pagination.html.twig', $arguments));
    }

    public function articleAction(string $id) : Response
    {
        $arguments = $this->defaultPageArguments();

        $arguments['article'] = $this->get('elife.api_sdk.blog_articles')->get($id);

        $arguments['contentHeader'] = $arguments['article']
            ->then($this->willConvertTo(ContentHeaderNonArticle::class));

        $arguments['leadParas'] = $arguments['article']
            ->then(Callback::methodEmptyOr('getImpactStatement', $this->willConvertTo(LeadParas::class)));

        $arguments['blocks'] = $arguments['article']
            ->then($this->willConvertContent());

        return new Response($this->get('templating')->render('::inside-elife-article.html.twig', $arguments));
    }
}
