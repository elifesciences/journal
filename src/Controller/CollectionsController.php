<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Collection;
use eLife\Journal\Helper\Callback;
use eLife\Journal\Helper\HasPages;
use eLife\Journal\Helper\Paginator;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use eLife\Patterns\ViewModel\LeadParas;
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
        $perPage = 6;

        $arguments = $this->defaultPageArguments();

        $latestResearch = $this->pagerfantaPromise(
            $this->get('elife.api_sdk.collections'),
            $page,
            $perPage
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
        $arguments['contentHeader'] = ContentHeaderNonArticle::basic($arguments['title']);

        return new Response($this->get('templating')->render('::collections.html.twig', $arguments));
    }

    public function collectionAction(Request $request, string $id) : Response
    {
        $collection = $this->get('elife.api_sdk.collections')
            ->get($id)
            ->otherwise($this->mightNotExist())
            ->then($this->checkSlug($request, Callback::method('getTitle')));

        $arguments = $this->defaultPageArguments($collection);

        $arguments['title'] = $collection
            ->then(Callback::method('getFullTitle'));

        $arguments['collection'] = $collection;

        $arguments['contentHeader'] = $arguments['collection']
            ->then($this->willConvertTo(ContentHeaderNonArticle::class));

        $arguments['leadParas'] = $arguments['collection']
            ->then(Callback::methodEmptyOr('getImpactStatement', $this->willConvertTo(LeadParas::class)));

        $arguments['collectionList'] = $arguments['collection']
            ->then(function (Collection $collection) {
                return ListingTeasers::basic(
                    $collection->getContent()->map($this->willConvertTo(Teaser::class))->toArray(),
                    'Collection'
                );
            });

        $arguments['multimedia'] = $arguments['collection']
            ->then(Callback::method('getPodcastEpisodes'))
            ->then(Callback::emptyOr(function (Sequence $podcastEpisodes) {
                return ListingTeasers::basic(
                    $podcastEpisodes->map($this->willConvertTo(Teaser::class, ['variant' => 'secondary']))->toArray(),
                    'Multimedia'
                );
            }));

        $arguments['related'] = $arguments['collection']
            ->then(Callback::method('getRelatedContent'))
            ->then(Callback::emptyOr(function (Sequence $relatedContent) {
                return ListingTeasers::basic(
                    $relatedContent->map($this->willConvertTo(Teaser::class, ['variant' => 'secondary']))->toArray(),
                    'Related'
                );
            }));

        $arguments['contributors'] = $arguments['collection']
            ->then(Callback::method('getCurators'))
            ->then(function (Sequence $curators) {
                return ListingProfileSnippets::basic(
                    $curators->map($this->willConvertTo(ProfileSnippet::class))->toArray(),
                    'Contributors'
                );
            });

        return new Response($this->get('templating')->render('::collection.html.twig', $arguments));
    }
}
