<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Collection;
use eLife\ApiSdk\Model\Identifier;
use eLife\Journal\Helper\Callback;
use eLife\Journal\Helper\HasPages;
use eLife\Journal\Helper\Paginator;
use eLife\Patterns\ViewModel\ContentHeader;
use eLife\Patterns\ViewModel\ContextualData;
use eLife\Patterns\ViewModel\ListHeading;
use eLife\Patterns\ViewModel\ListingProfileSnippets;
use eLife\Patterns\ViewModel\ListingTeasers;
use eLife\Patterns\ViewModel\ProfileSnippet;
use eLife\Patterns\ViewModel\Teaser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class CollectionsController extends Controller
{
    use HasPages;

    public function listAction(Request $request) : Response
    {
        $page = (int) $request->query->get('page', 1);
        $perPage = 10;

        $arguments = $this->defaultPageArguments($request);

        $latestResearch = $this->pagerfantaPromise(
            $this->get('elife.api_sdk.collections'),
            $page,
            $perPage,
            $this->willConvertTo(Teaser::class)
        );

        $arguments['title'] = 'Collections';

        $arguments['paginator'] = $this->paginator(
            $latestResearch,
            $request,
            'Browse our collections',
            'collections'
        );

        $arguments['listing'] = $arguments['paginator']
            ->then($this->willConvertTo(ListingTeasers::class, ['emptyText' => 'No collections available.']));

        if (1 === $page) {
            return $this->createFirstPage($arguments);
        }

        return $this->createSubsequentPage($request, $arguments);
    }

    private function createFirstPage(array $arguments) : Response
    {
        $arguments['contentHeader'] = new ContentHeader($arguments['title']);

        return new Response($this->get('templating')->render('::collections.html.twig', $arguments));
    }

    public function collectionAction(Request $request, string $id) : Response
    {
        $arguments['item'] = $this->get('elife.api_sdk.collections')
            ->get($id)
            ->otherwise($this->mightNotExist())
            ->then($this->checkSlug($request, Callback::method('getTitle')));

        $arguments = $this->defaultPageArguments($request, $arguments['item']);

        $arguments['title'] = $arguments['item']
            ->then(Callback::method('getTitle'));

        $arguments['pageViews'] = $this->get('elife.api_sdk.metrics')
            ->totalPageViews(Identifier::collection($id))
            ->otherwise($this->mightNotExist())
            ->otherwise($this->softFailure('Failed to load page views count'));

        $arguments['contextualData'] = $arguments['pageViews']
            ->then(Callback::emptyOr(function (int $pageViews) {
                return ContextualData::withMetrics([sprintf('Views %s', number_format($pageViews))]);
            }));

        $arguments['contentHeader'] = $arguments['item']
            ->then($this->willConvertTo(ContentHeader::class));

        $arguments['body'] = $arguments['item']
            ->then(function (Collection $collection) {
                if ($collection->getSummary()->notEmpty()) {
                    yield from $collection->getSummary()->map($this->willConvertTo());
                }

                yield ListingTeasers::basic(
                    $collection->getContent()->map($this->willConvertTo(Teaser::class))->toArray(),
                    new ListHeading('Collection')
                );
            });

        $arguments['multimedia'] = $arguments['item']
            ->then(Callback::method('getPodcastEpisodes'))
            ->then(Callback::emptyOr(function (Sequence $podcastEpisodes) {
                return ListingTeasers::basic(
                    $podcastEpisodes->map($this->willConvertTo(Teaser::class, ['variant' => 'secondary']))->toArray(),
                    new ListHeading('Multimedia')
                );
            }));

        $arguments['related'] = $arguments['item']
            ->then(Callback::method('getRelatedContent'))
            ->then(Callback::emptyOr(function (Sequence $relatedContent) {
                return ListingTeasers::basic(
                    $relatedContent->map($this->willConvertTo(Teaser::class, ['variant' => 'secondary']))->toArray(),
                    new ListHeading('Related')
                );
            }));

        $arguments['contributors'] = $arguments['item']
            ->then(Callback::method('getCurators'))
            ->then(function (Sequence $curators) {
                return ListingProfileSnippets::basic(
                    $curators->map($this->willConvertTo(ProfileSnippet::class))->toArray(),
                    new ListHeading('Contributors')
                );
            });

        return new Response($this->get('templating')->render('::collection.html.twig', $arguments));
    }
}
