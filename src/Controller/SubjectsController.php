<?php

namespace eLife\Journal\Controller;

use eLife\ApiClient\ApiClient\SearchClient;
use eLife\ApiClient\ApiClient\SubjectsClient;
use eLife\ApiClient\Exception\BadResponse;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\Patterns\ViewModel\BackgroundImage;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use eLife\Patterns\ViewModel\LeadPara;
use eLife\Patterns\ViewModel\LeadParas;
use eLife\Patterns\ViewModel\ListHeading;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

final class SubjectsController extends Controller
{
    public function subjectAction(string $id) : Response
    {
        $page = 1;
        $perPage = 6;

        $arguments = $this->defaultPageArguments();

        $arguments['subject'] = $this->get('elife.api_client.subjects')
            ->getSubject(['Accept' => new MediaType(SubjectsClient::TYPE_SUBJECT, 1)], $id)
            ->otherwise(function (Throwable $e) {
                if ($e instanceof BadResponse && 404 === $e->getResponse()->getStatusCode()) {
                    throw new NotFoundHttpException('Subject not found', $e);
                }
            });

        $arguments['contentHeader'] = $arguments['subject']
            ->then(function (Result $subject) {
                return ContentHeaderNonArticle::subject($subject['name'], false, null,
                    new BackgroundImage(
                        $subject['image']['sizes']['2:1'][900],
                        $subject['image']['sizes']['2:1'][1800]
                    )
                );
            });

        $arguments['lead_paras'] = $arguments['subject']
            ->then(function (Result $result) {
                return new LeadParas([new LeadPara($result['impactStatement'])]);
            })
            ->otherwise(function () {
                return null;
            });

        $arguments['latestArticlesHeading'] = new ListHeading('Latest articles');
        $arguments['latestArticles'] = $this->get('elife.api_client.search')
            ->query(['Accept' => new MediaType(SearchClient::TYPE_SEARCH, 1)], '', $page, $perPage, 'date',
                true, [$id])
            ->then(function (Result $result) use ($arguments) {
                if (empty($result['items'])) {
                    return null;
                }

                return $this->get('elife.journal.view_model.factory.listing_teaser')
                    ->forResult($result, $arguments['latestArticlesHeading']['heading'])
                    ;
            });

        return new Response($this->get('templating')->render('::subject.html.twig', $arguments));
    }
}
