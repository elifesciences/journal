<?php

namespace eLife\Journal\Controller;

use eLife\Patterns\ViewModel\BackgroundImage;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use Symfony\Component\HttpFoundation\Response;

final class CommunityController extends Controller
{
    public function listAction() : Response
    {
        $page = (int) $request->query->get('page', 1);
        $perPage = 6;
        $arguments = $this->defaultPageArguments();

        $arguments['contentHeader'] = ContentHeaderNonArticle::basic('Community', true, null, null, null,
            new BackgroundImage(
                $this->get('puli.url_generator')->generateUrl('/elife/journal/images/banners/community-lo-res.jpg'),
                $this->get('puli.url_generator')->generateUrl('/elife/journal/images/banners/community-hi-res.jpg')
            ));

        // TODO: duplication of PagerFanta instantiation
        $latestCommunity = promise_for($this->get('elife.api_sdk.community'))
            ->then(function (Sequence $sequence) use ($page, $perPage) {
                $pagerfanta = new Pagerfanta(new SequenceAdapter($sequence, $this->willConvertTo(Teaser::class)));
                $pagerfanta->setMaxPerPage($perPage)->setCurrentPage($page);

                return $pagerfanta;
            });

        //$arguments['paginator'] = $latestResearch
        //    ->then(function (Pagerfanta $pagerfanta) use ($request) {
        //        return new Paginator(
        //            'Browse our collections',
        //            $pagerfanta,
        //            function (int $page = null) use ($request) {
        //                $routeParams = $request->attributes->get('_route_params');
        //                $routeParams['page'] = $page;

        //                return $this->get('router')->generate('collections', $routeParams);
        //            }
        //        );
        //    });

        //$arguments['listing'] = $arguments['paginator']
        //    ->then($this->willConvertTo(ListingTeasers::class, ['type' => 'collections']));

        //if (1 === $page) {
        //    return $this->createFirstPage($arguments);
        //}

        //return $this->createSubsequentPage($request, $arguments);
        //
        return new Response($this->get('templating')->render('::community.html.twig', $arguments));
    }
}
