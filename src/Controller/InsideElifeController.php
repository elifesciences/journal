<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\BlogArticle;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use eLife\Patterns\ViewModel\LeadPara;
use eLife\Patterns\ViewModel\LeadParas;
use eLife\Patterns\ViewModel\ListHeading;
use eLife\Patterns\ViewModel\ListingTeasers;
use eLife\Patterns\ViewModel\Teaser;
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
        $arguments['latest'] = $this->get('elife.api_sdk.blog_articles')
            ->slice(($page * $perPage) - $perPage, $perPage)
            ->then(function (Sequence $result) use ($arguments) {
                if ($result->isEmpty()) {
                    return null;
                }

                return ListingTeasers::basic(
                    $result->map(function (BlogArticle $article) {
                        return $this->get('elife.journal.view_model.converter')->convert($article, Teaser::class);
                    })->toArray(),
                    $arguments['latestHeading']['heading']
                );
            });

        return new Response($this->get('templating')->render('::inside-elife.html.twig', $arguments));
    }

    public function articleAction(string $id) : Response
    {
        $arguments = $this->defaultPageArguments();

        $arguments['article'] = $this->get('elife.api_sdk.blog_articles')->get($id);

        $arguments['contentHeader'] = $arguments['article']
            ->then(function (BlogArticle $article) {
                return $this->get('elife.journal.view_model.converter')->convert($article, ContentHeaderNonArticle::class);
            });

        $arguments['leadParas'] = $arguments['article']
            ->then(function (BlogArticle $article) {
                return new LeadParas([new LeadPara($article->getImpactStatement())]);
            })
            ->otherwise(function () {
                return null;
            });

        $arguments['blocks'] = $arguments['article']
            ->then(function (BlogArticle $article) {
                return $article->getContent()->map(function (Block $block) {
                    return $this->get('elife.journal.view_model.converter')->convert($block);
                });
            });

        return new Response($this->get('templating')->render('::inside-elife-article.html.twig', $arguments));
    }
}
