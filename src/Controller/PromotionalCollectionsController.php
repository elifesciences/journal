<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Identifier;
use eLife\ApiSdk\Model\PromotionalCollection;
use eLife\Journal\Helper\Callback;
use eLife\Patterns\ViewModel\ContentHeader;
use eLife\Patterns\ViewModel\ContextualData;
use eLife\Patterns\ViewModel\ListHeading;
use eLife\Patterns\ViewModel\ListingProfileSnippets;
use eLife\Patterns\ViewModel\ListingTeasers;
use eLife\Patterns\ViewModel\ProfileSnippet;
use eLife\Patterns\ViewModel\Teaser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class PromotionalCollectionsController extends Controller
{
    public function promotionalCollectionAction(Request $request, string $id) : Response
    {
        $arguments['item'] = $this->get('elife.api_sdk.promotional-collections')
            ->get($id)
            ->otherwise($this->mightNotExist())
            ->then($this->checkSlug($request, Callback::method('getTitle')));

        $arguments = $this->defaultPageArguments($request, $arguments['item']);

        $arguments['title'] = $arguments['item']
            ->then(Callback::method('getTitle'));

        $arguments['pageViews'] = $this->get('elife.api_sdk.metrics')
            ->totalPageViews(Identifier::promotionalCollection($id))
            ->otherwise($this->mightNotExist())
            ->otherwise($this->softFailure('Failed to load page views count'));

        $arguments['contextualData'] = $arguments['pageViews']
            ->then(Callback::emptyOr(function (int $pageViews) {
                return ContextualData::withMetrics([sprintf('Views %s', number_format($pageViews))]);
            }));

        $arguments['contentHeader'] = $arguments['item']
            ->then($this->willConvertTo(ContentHeader::class));

        $arguments['body'] = $arguments['item']
            ->then(function (PromotionalCollection $promotionalCollection) {
                if ($promotionalCollection->getSummary()->notEmpty()) {
                    yield from $promotionalCollection->getSummary()->map($this->willConvertTo());
                }

                yield ListingTeasers::basic(
                    $promotionalCollection->getContent()->map($this->willConvertTo(Teaser::class))->toArray(),
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

        $arguments['editors'] = $arguments['item']
            ->then(Callback::method('getEditors'))
            ->then(Callback::emptyOr(function (Sequence $editors) {
                return ListingProfileSnippets::basic(
                    $editors->map($this->willConvertTo(ProfileSnippet::class))->toArray(),
                    new ListHeading('Local editors')
                );
            }));

        return new Response($this->get('templating')->render('::promotional-collection.html.twig', $arguments));
    }
}
