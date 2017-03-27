<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\Journal\Helper\Callback;
use eLife\Journal\Helper\HasPages;
use eLife\Journal\Helper\Paginator;
use eLife\Patterns\ViewModel\BackgroundImage;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use eLife\Patterns\ViewModel\ListingTeasers;
use eLife\Patterns\ViewModel\Teaser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class CommunityController extends Controller
{
    use HasPages;

    public function listAction(Request $request) : Response
    {
        $page = (int) $request->query->get('page', 1);
        $perPage = 6;
        $arguments = $this->defaultPageArguments();

        $latestCommunity = $this->pagerfantaPromise(
            $this->get('elife.api_sdk.community'),
            $page,
            $perPage
        );

        $arguments['title'] = 'Community';

        $arguments['paginator'] = $this->paginator(
            $latestCommunity,
            $request,
            'Browse our community articles',
            'community'
        );

        $arguments['listing'] = $arguments['paginator']
            ->then($this->willConvertTo(ListingTeasers::class, ['emptyText' => 'No community articles available.']));

        if (1 === $page) {
            return $this->createFirstPage($arguments);
        }

        return $this->createSubsequentPage($request, $arguments);
    }

    private function createFirstPage(array $arguments) : Response
    {
        $arguments['contentHeader'] = ContentHeaderNonArticle::basic('Community', true, null, null, null,
            new BackgroundImage(
                $this->get('assets.packages')->getUrl('assets/images/banners/community-lo-res.jpg'),
                $this->get('assets.packages')->getUrl('assets/images/banners/community-hi-res.jpg')
            ));

        $arguments['highlights'] = (new PromiseSequence($this->get('elife.api_sdk.highlights')
            ->get('community')))
            ->then(Callback::emptyOr(function (Sequence $result) {
                return ListingTeasers::basic($result->map($this->willConvertTo(Teaser::class, ['variant' => 'secondary']))->toArray(), 'Highlights');
            }))
            ->otherwise($this->softFailure('Failed to load community highlights'));

        return new Response($this->get('templating')->render('::community.html.twig', $arguments));
    }
}
