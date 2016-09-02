<?php

namespace eLife\Journal\Controller;

use DateTimeImmutable;
use eLife\ApiClient\ApiClient\BlogClient;
use eLife\ApiClient\Exception\BadResponse;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use eLife\Patterns\ViewModel\Date;
use eLife\Patterns\ViewModel\LeadPara;
use eLife\Patterns\ViewModel\LeadParas;
use eLife\Patterns\ViewModel\Link;
use eLife\Patterns\ViewModel\ListHeading;
use eLife\Patterns\ViewModel\Meta;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

final class InsideElifeController extends Controller
{
    public function listAction() : Response
    {
        $page = 1;
        $perPage = 6;

        $arguments = $this->defaultPageArguments();

        $arguments['contentHeader'] = ContentHeaderNonArticle::basic('Inside eLife');

        $arguments['latestHeading'] = new ListHeading('Latest');
        $arguments['latest'] = $this->get('elife.api_client.blog')
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

    public function articleAction(string $id) : Response
    {
        $arguments = $this->defaultPageArguments();

        $arguments['article'] = $this->get('elife.api_client.blog')
            ->getArticle(['Accept' => new MediaType(BlogClient::TYPE_BLOG_ARTICLE, 1)], $id)
            ->otherwise(function (Throwable $e) {
                if ($e instanceof BadResponse && 404 === $e->getResponse()->getStatusCode()) {
                    throw new NotFoundHttpException('Article not found', $e);
                }

                throw $e;
            });

        $arguments['contentHeader'] = $arguments['article']
            ->then(function (Result $article) {
                return ContentHeaderNonArticle::basic($article['title'], false, null, null,
                    Meta::withLink(
                        new Link('Inside eLife', $this->get('router')->generate('inside-elife')),
                        new Date(DateTimeImmutable::createFromFormat(DATE_ATOM, $article['published']))
                    )
                );
            });

        $arguments['leadParas'] = $arguments['article']
            ->then(function (Result $episode) {
                return new LeadParas([new LeadPara($episode['impactStatement'])]);
            })
            ->otherwise(function () {
                return null;
            });

        $arguments['blocks'] = $arguments['article']
            ->then(function (Result $article) {
                return $this->get('elife.website.view_model.block_converter')->handleBlocks(...$article['content']);
            });

        return new Response($this->get('templating')->render('::inside-elife-article.html.twig', $arguments));
    }
}
