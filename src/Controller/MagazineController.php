<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Collection\Sequence;
use eLife\Journal\Helper\Callback;
use eLife\Journal\Helper\Paginator;
use eLife\Journal\Pagerfanta\SequenceAdapter;
use eLife\Patterns\ViewModel\AudioPlayer;
use eLife\Patterns\ViewModel\BackgroundImage;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use eLife\Patterns\ViewModel\Link;
use eLife\Patterns\ViewModel\ListingTeasers;
use eLife\Patterns\ViewModel\SectionListing;
use eLife\Patterns\ViewModel\SectionListingLink;
use eLife\Patterns\ViewModel\SeeMoreLink;
use eLife\Patterns\ViewModel\Teaser;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function GuzzleHttp\Promise\promise_for;

final class MagazineController extends Controller
{
    public function listAction(Request $request) : Response
    {
        $page = (int) $request->query->get('page', 1);
        $perPage = 6;

        $arguments = $this->defaultPageArguments();

        $latestResearch = promise_for($this->get('elife.api_sdk.search')
            ->forType('editorial', 'insight', 'feature', 'collection', 'interview', 'podcast-episode')
            ->sortBy('date'))
            ->then(function (Sequence $sequence) use ($page, $perPage) {
                $pagerfanta = new Pagerfanta(new SequenceAdapter($sequence, $this->willConvertTo(Teaser::class)));
                $pagerfanta->setMaxPerPage($perPage)->setCurrentPage($page);

                return $pagerfanta;
            });

        $arguments['title'] = 'Magazine';

        $arguments['paginator'] = $latestResearch
            ->then(function (Pagerfanta $pagerfanta) use ($request) {
                return new Paginator(
                    'Browse our latest Magazine content',
                    $pagerfanta,
                    function (int $page = null) use ($request) {
                        $routeParams = $request->attributes->get('_route_params');
                        $routeParams['page'] = $page;

                        return $this->get('router')->generate('magazine', $routeParams);
                    }
                );
            });

        $arguments['listing'] = $arguments['paginator']
            ->then($this->willConvertTo(ListingTeasers::class, ['type' => 'articles']));

        if (1 === $page) {
            return $this->createFirstPage($request, $arguments);
        }

        return $this->createSubsequentPage($request, $arguments);
    }

    private function createFirstPage(Request $request, array $arguments) : Response
    {
        $arguments['contentHeader'] = ContentHeaderNonArticle::basic('Magazine', true,
            'Highlighting the latest research and giving a voice to life and biomedical scientists.',
            null, null, new BackgroundImage(
                $this->get('assets.packages')->getUrl('assets/images/banners/magazine-lo-res.jpg'),
                $this->get('assets.packages')->getUrl('assets/images/banners/magazine-hi-res.jpg')
            ));

        $arguments['audio_player'] = $this->get('elife.api_sdk.podcast_episodes')
            ->slice(0, 1)
            ->then(Callback::method('offsetGet', 0))
            ->then(Callback::emptyOr($this->willConvertTo(AudioPlayer::class)))
            ->otherwise($this->softFailure('Failed to load podcast episode audio player'));

        $arguments['highlights'] = $this->get('elife.api_sdk.highlights')
            ->get('magazine')
            ->slice(0, 6)
            ->map($this->willConvertTo(Teaser::class, ['variant' => 'secondary']))
            ->then(function (Sequence $highlights) {
                return ListingTeasers::forHighlights($highlights->toArray(), 'Highlights', 'highlights');
            })
            ->otherwise($this->softFailure('Failed to load highlights for magazine'));

        $events = $this->get('elife.api_sdk.events')
            ->forType('open')
            ->reverse();

        $arguments['menuLink'] = new SectionListingLink('All sections', 'sections');

        $arguments['menu'] = new SectionListing('sections', [
            new Link('Editorials', $this->get('router')->generate('article-type', ['type' => 'editorial'])),
            new Link('Insights', $this->get('router')->generate('article-type', ['type' => 'insight'])),
            new Link('Feature articles', $this->get('router')->generate('article-type', ['type' => 'feature'])),
            new Link('Podcast', $this->get('router')->generate('podcast')),
            new Link('Collections', $this->get('router')->generate('collections')),
            new Link('Community', $this->get('router')->generate('community')),
        ], true);

        $arguments['events'] = $events
            ->slice(0, 3)
            ->then(Callback::emptyOr(function (Sequence $result) use ($events) {
                $items = $result->map($this->willConvertTo(Teaser::class, ['variant' => 'secondary']))->toArray();
                $heading = 'Events';

                if (count($events) > 3) {
                    return ListingTeasers::withSeeMore(
                        $items,
                        new SeeMoreLink(new Link('See more events', $this->get('router')->generate('events'))),
                        $heading
                    );
                }

                return ListingTeasers::basic($items, $heading);
            }))
            ->otherwise($this->softFailure('Failed to load events'));

        $arguments['elifeDigests'] = $this->get('elife.api_sdk.medium_articles')
            ->slice(0, 3)
            ->then(Callback::emptyOr(function (Sequence $result) {
                return ListingTeasers::withSeeMore(
                    $result->map($this->willConvertTo(Teaser::class, ['variant' => 'secondary']))->toArray(),
                    new SeeMoreLink(new Link('See more eLife digests on Medium', 'https://medium.com/@elife')),
                    'eLife digests'
                );
            }))
            ->otherwise($this->softFailure('Failed to load Medium articles'));

        return new Response($this->get('templating')->render('::magazine.html.twig', $arguments));
    }
}
