<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\BlogArticle;
use eLife\Journal\Helper\Callback;
use eLife\Journal\Helper\Paginator;
use eLife\Journal\Pagerfanta\SequenceAdapter;
use eLife\Patterns\ViewModel\ContentHeader;
use eLife\Patterns\ViewModel\ContextualData;
use eLife\Patterns\ViewModel\ListingTeasers;
use eLife\Patterns\ViewModel\SpeechBubble;
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
        $perPage = 10;

        $arguments = $this->defaultPageArguments($request);

        $latest = promise_for($this->get('elife.api_sdk.blog_articles'))
            ->then(function (Sequence $sequence) use ($page, $perPage) {
                $pagerfanta = new Pagerfanta(new SequenceAdapter($sequence, $this->willConvertTo(Teaser::class)));
                $pagerfanta->setMaxPerPage($perPage)->setCurrentPage($page);

                return $pagerfanta;
            });

        $arguments['title'] = 'Inside eLife';

        $arguments['paginator'] = $latest
            ->then(function (Pagerfanta $pagerfanta) use ($request) {
                return new Paginator(
                    'Browse Inside eLife',
                    $pagerfanta,
                    function (int $page = null) use ($request) {
                        $routeParams = $request->attributes->get('_route_params');
                        $routeParams['page'] = $page;

                        return $this->get('router')->generate('inside-elife', $routeParams);
                    }
                );
            });

        $arguments['listing'] = $arguments['paginator']
            ->then($this->willConvertTo(ListingTeasers::class, ['heading' => 'Latest', 'emptyText' => 'No articles available.']));

        if (1 === $page) {
            return $this->createFirstPage($arguments);
        }

        return $this->createSubsequentPage($request, $arguments);
    }

    private function createFirstPage(array $arguments) : Response
    {
        $arguments['contentHeader'] = new ContentHeader($arguments['title']);

        return new Response($this->get('templating')->render('::inside-elife.html.twig', $arguments));
    }

    public function articleAction(Request $request, string $id) : Response
    {
        $arguments['item'] = $this->get('elife.api_sdk.blog_articles')
            ->get($id)
            ->otherwise($this->mightNotExist())
            ->then($this->checkSlug($request, Callback::method('getTitle')));

        $arguments = $this->defaultPageArguments($request, $arguments['item']);

        $arguments['title'] = $arguments['item']
            ->then(Callback::method('getTitle'));

        $arguments['contentHeader'] = $arguments['item']
            ->then($this->willConvertTo(ContentHeader::class));

        $arguments['contextualData'] = $arguments['item']
            ->then($this->ifGranted(['FEATURE_CAN_USE_HYPOTHESIS'], function (BlogArticle $article) {
                return ContextualData::annotationsOnly(SpeechBubble::forContextualData());
            }));

        $arguments['blocks'] = $arguments['item']
            ->then($this->willConvertContent())
            ->then(function (Sequence $blocks) {
                if (!$this->isGranted('FEATURE_CAN_USE_HYPOTHESIS')) {
                    return $blocks;
                }

                return $blocks->prepend(SpeechBubble::forArticleBody());
            });

        return new Response($this->get('templating')->render('::inside-elife-article.html.twig', $arguments));
    }
}
