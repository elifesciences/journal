<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Identifier;
use eLife\Journal\Helper\Callback;
use eLife\Journal\Helper\Paginator;
use eLife\Journal\Pagerfanta\SequenceAdapter;
use eLife\Patterns\ViewModel\ContentHeader;
use eLife\Patterns\ViewModel\ContextualData;
use eLife\Patterns\ViewModel\GridListing;
use eLife\Patterns\ViewModel\ListingTeasers;
use eLife\Patterns\ViewModel\SpeechBubble;
use eLife\Patterns\ViewModel\Teaser;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use function GuzzleHttp\Promise\promise_for;

final class DigestsController extends Controller
{
    public function listAction(Request $request) : Response
    {
        if (!$this->isGranted('FEATURE_DIGEST_CHANNEL')) {
            throw new NotFoundHttpException('Not allowed to see the Digest channel');
        }

        $page = (int) $request->query->get('page', 1);
        $perPage = 8;

        $arguments = $this->defaultPageArguments($request);

        $latest = promise_for($this->get('elife.api_sdk.digests'))
            ->then(function (Sequence $sequence) use ($page, $perPage) {
                $pagerfanta = new Pagerfanta(new SequenceAdapter($sequence, $this->willConvertTo(Teaser::class, ['variant' => 'grid'])));
                $pagerfanta->setMaxPerPage($perPage)->setCurrentPage($page);

                return $pagerfanta;
            });

        $arguments['title'] = 'eLife Science Digests';

        $arguments['paginator'] = $latest
            ->then(function (Pagerfanta $pagerfanta) use ($request) {
                return new Paginator(
                    'Browse our Science Digests',
                    $pagerfanta,
                    function (int $page = null) use ($request) {
                        $routeParams = $request->attributes->get('_route_params');
                        $routeParams['page'] = $page;

                        return $this->get('router')->generate('digests', $routeParams);
                    }
                );
            });

        $arguments['listing'] = $arguments['paginator']
            ->then($this->willConvertTo(GridListing::class, ['heading' => 'Latest', 'type' => 'digests']));

        if (1 === $page) {
            return $this->createFirstPage($arguments);
        }

        return $this->createSubsequentPage($request, $arguments);
    }

    private function createFirstPage(array $arguments) : Response
    {
        $arguments['contentHeader'] = new ContentHeader($arguments['title'], null,
            'Short article written in plain language to explain recent eLife papers to a broad audience.');

        return new Response($this->get('templating')->render('::digests.html.twig', $arguments));
    }

    public function digestAction(Request $request, string $id) : Response
    {
        if (!$this->isGranted('FEATURE_DIGEST_CHANNEL')) {
            throw new NotFoundHttpException('Not allowed to see the Digest channel');
        }

        $arguments['item'] = $this->get('elife.api_sdk.digests')
            ->get($id)
            ->otherwise($this->mightNotExist())
            ->then($this->checkSlug($request, Callback::method('getTitle')));

        $arguments = $this->defaultPageArguments($request, $arguments['item']);

        $arguments['title'] = $arguments['item']
            ->then(Callback::method('getTitle'));

        $arguments['contentHeader'] = $arguments['item']
            ->then($this->willConvertTo(ContentHeader::class));

        $arguments['pageViews'] = $this->get('elife.api_sdk.metrics')
            ->totalPageViews(Identifier::digest($id))
            ->otherwise($this->mightNotExist())
            ->otherwise($this->softFailure('Failed to load page views count'));

        $arguments['contextualData'] = $arguments['pageViews']
            ->then(Callback::emptyOr(function (int $pageViews) {
                return ContextualData::withMetrics([sprintf('Views %s', number_format($pageViews))], null, null, SpeechBubble::forContextualData());
            }, function () {
                return ContextualData::annotationsOnly(SpeechBubble::forContextualData());
            }));

        $arguments['blocks'] = $arguments['item']
            ->then($this->willConvertContent())
            ->then(function (Sequence $blocks) {
                return $blocks->prepend(SpeechBubble::forArticleBody());
            });

        $arguments['relatedContent'] = (new PromiseSequence($arguments['item']
            ->then(Callback::method('getRelatedContent'))))
            ->map($this->willConvertTo(Teaser::class, ['variant' => 'relatedItem', 'from' => 'interview', 'related' => true]))
            ->then(function (Sequence $collections) {
                return ListingTeasers::basic($collections->toArray());
            });

        return new Response($this->get('templating')->render('::digest.html.twig', $arguments));
    }
}
