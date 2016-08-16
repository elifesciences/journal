<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\ApiClient\BlogClient;
use eLife\ApiSdk\MediaType;
use eLife\ApiSdk\Result;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use eLife\Patterns\ViewModel\ListHeading;
use Symfony\Component\HttpFoundation\Response;

final class InsideElifeController extends Controller
{
    public function listAction() : Response
    {
        $page = 1;
        $perPage = 6;

        $arguments = $this->defaultPageArguments();

        $arguments['contentHeader'] = ContentHeaderNonArticle::basic('Inside eLife');

        $arguments['latestHeading'] = new ListHeading('Latest');
        $arguments['latest'] = $this->get('elife.api_sdk.blog')
            ->listArticles(['Accept' => new MediaType(BlogClient::TYPE_BLOG_ARTICLE_LIST, 1)], $page, $perPage)
            ->then(function (Result $result) use ($arguments) {
                if (empty($result['items'])) {
                    return null;
                }

                $items = array_map(function (array $item) {
                    $item['type'] = 'blog-article';

                    return $item;
                }, $result['items']);

                return $this->get('elife.journal.view_model.factory.listing_teaser')
                    ->forItems($items, $arguments['latestHeading']['heading']);
            })
        ;

        return new Response($this->get('templating')->render('::inside-elife.html.twig', $arguments));
    }
}
