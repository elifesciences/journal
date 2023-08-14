<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\Identifier;
use eLife\Journal\Helper\Callback;
use eLife\Journal\Helper\Paginator;
use eLife\Journal\Pagerfanta\SequenceAdapter;
use eLife\Patterns\ViewModel\CaptionedAsset;
use eLife\Patterns\ViewModel\ContentHeader;
use eLife\Patterns\ViewModel\ContentHeaderNew;
use eLife\Patterns\ViewModel\GridListing;
use eLife\Patterns\ViewModel\ListingTeasers;
use eLife\Patterns\ViewModel\Teaser;
use function GuzzleHttp\Promise\all;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function GuzzleHttp\Promise\promise_for;

final class DigestsController extends Controller
{
    public function listAction(Request $request) : Response
    {
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
            'Cutting jargon and putting research in context, <a href="https://doi.org/10.7554/eLife.25410">digests</a> showcase some of the latest articles published in eLife.');

        return new Response($this->get('templating')->render('::digests.html.twig', $arguments));
    }

    public function digestAction(Request $request, string $id) : Response
    {
        $arguments['item'] = $this->get('elife.api_sdk.digests')
            ->get($id)
            ->otherwise($this->mightNotExist())
            ->then($this->checkSlug($request, Callback::method('getTitle')));

        $arguments = $this->defaultPageArguments($request, $arguments['item']);
        
        $arguments['isMagazine'] = true;

        $arguments['title'] = $arguments['item']
            ->then(Callback::method('getTitle'));

        $arguments['pageViews'] = $this->get('elife.api_sdk.metrics')
            ->totalPageViews(Identifier::digest($id))
            ->otherwise($this->mightNotExist())
            ->otherwise($this->softFailure('Failed to load page views count'));

        $arguments = array_merge($arguments, $this->magazinePageArguments($arguments, 'digest'));

        $arguments['contentHeader'] = all(['item' => $arguments['item'], 'metrics' => $arguments['contextualDataMetrics']])
            ->then(function (array $parts) {
                return $this->convertTo($parts['item'], ContentHeaderNew::class, ['metrics' => $parts['metrics']]);
            });

        $arguments['blocks'] = $arguments['item']
            ->then($this->willConvertContent());

        $arguments['relatedContent'] = (new PromiseSequence($arguments['item']
            ->then(Callback::method('getRelatedContent'))))
            ->map($this->willConvertTo(Teaser::class, ['variant' => 'relatedItem', 'from' => 'interview', 'related' => true]))
            ->then(function (Sequence $collections) {
                return ListingTeasers::basic($collections->toArray());
            });

        $arguments['socialImage'] = all(['blocks' => $arguments['blocks']])
            ->then(function($parts) {
                return $parts['blocks']->filter(Callback::isInstanceOf(CaptionedAsset::class))->offsetGet(0);
            });

        $arguments['blocks'] = all(['blocks' => $arguments['blocks']])
            ->then(function($parts) {
                return $parts['blocks']->filter(function($image) {
                    return !($image instanceof CaptionedAsset);
                });
            });
            
        return new Response($this->get('templating')->render('::digest.html.twig', $arguments));
    }
}
