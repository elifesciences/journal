<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\ApiClient\SearchClient;
use eLife\ApiSdk\MediaType;
use eLife\ApiSdk\Result;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use eLife\Patterns\ViewModel\Link;
use eLife\Patterns\ViewModel\ListHeading;
use eLife\Patterns\ViewModel\SeeMoreLink;
use Symfony\Component\HttpFoundation\Response;

final class HomeController extends Controller
{
    public function homeAction() : Response
    {
        $page = 1;
        $perPage = 6;

        $arguments = $this->defaultPageArguments();

        $arguments['contentHeader'] = ContentHeaderNonArticle::basic('eLife');

        $arguments['latestResearchHeading'] = new ListHeading('Latest research');
        $arguments['latestResearch'] = $this->get('elife.api_sdk.search')
            ->query(['Accept' => new MediaType(SearchClient::TYPE_SEARCH, 1)], '', $page, $perPage, 'date', true, [],
                [
                    'research-advance',
                    'research-article',
                    'research-exchange',
                    'short-report',
                    'tools-resources',
                    'replication-study',
                ])
            ->then(function (Result $result) use ($arguments) {
                if (empty($result['items'])) {
                    return null;
                }

                return $this->get('elife.journal.view_model.factory.listing_teaser')
                    ->forResult($result, $arguments['latestResearchHeading']['heading']);
            })
        ;

        $arguments['magazine'] = $this->get('elife.api_sdk.search')
            ->query(['Accept' => new MediaType(SearchClient::TYPE_SEARCH, 1)], '', 1, 7, 'date', true, [],
                ['editorial', 'insight', 'feature', 'collection', 'interview', 'podcast-episode'])
            ->then(function (Result $result) use ($arguments) {
                if (empty($result['items'])) {
                    return null;
                }

                return $this->get('elife.journal.view_model.factory.listing_teaser_secondary')
                    ->forResult(
                        $result,
                        'Magazine',
                        new SeeMoreLink(
                            new Link(
                                'See more Magazine articles',
                                $this->get('router')->generate('magazine')
                            )
                        )
                    );
            })
            ->otherwise(function () {
                return null;
            });

        return new Response($this->get('templating')->render('::home.html.twig', $arguments));
    }
}
