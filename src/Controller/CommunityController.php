<?php

namespace eLife\Journal\Controller;

use eLife\Journal\Helper\HasPages;
use eLife\Journal\Helper\Paginator;
use eLife\Patterns\ViewModel\BackgroundImage;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use eLife\Patterns\ViewModel\ListingTeasers;
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

        $arguments['contentHeader'] = ContentHeaderNonArticle::basic('Community', true, null, null, null,
            new BackgroundImage(
                $this->get('puli.url_generator')->generateUrl('/elife/journal/images/banners/community-lo-res.jpg'),
                $this->get('puli.url_generator')->generateUrl('/elife/journal/images/banners/community-hi-res.jpg')
            ));

        $latestCommunity = $this->pagerfantaPromise(
            $this->get('elife.api_sdk.community'),
            $page,
            $perPage
        );

        $arguments['title'] = 'Community';

        $arguments['paginator'] = $this->paginator(
            $latestCommunity,
            $request,
            'Browse our community listings',
            'community'
        );

        $arguments['listing'] = $arguments['paginator']
            ->then($this->willConvertTo(ListingTeasers::class, ['type' => 'community content']));

        if (1 === $page) {
            return $this->createFirstPage($arguments);
        }

        return $this->createSubsequentPage($request, $arguments);
    }

    private function createFirstPage(array $arguments) : Response
    {
        $arguments['contentHeader'] = ContentHeaderNonArticle::basic('eLife community');

        return new Response($this->get('templating')->render('::community.html.twig', $arguments));
    }
}
