<?php

namespace eLife\Journal\Controller;

use DateTimeImmutable;
use eLife\ApiSdk\ApiClient\InterviewsClient;
use eLife\ApiSdk\Exception\BadResponse;
use eLife\ApiSdk\MediaType;
use eLife\ApiSdk\Result;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use eLife\Patterns\ViewModel\Date;
use eLife\Patterns\ViewModel\LeadPara;
use eLife\Patterns\ViewModel\LeadParas;
use eLife\Patterns\ViewModel\Meta;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

final class InterviewsController extends Controller
{
    public function interviewAction(string $id) : Response
    {
        $arguments = $this->defaultPageArguments();

        $arguments['interview'] = $this->get('elife.api_sdk.interviews')
            ->getInterview(['Accept' => new MediaType(InterviewsClient::TYPE_INTERVIEW, 1)], $id)
            ->otherwise(function (Throwable $e) {
                if ($e instanceof BadResponse && 404 === $e->getResponse()->getStatusCode()) {
                    throw new NotFoundHttpException('Interview not found', $e);
                }
            });

        $arguments['contentHeader'] = $arguments['interview']
            ->then(function (Result $interview) {
                return ContentHeaderNonArticle::basic($interview['title'], false,
                    'An interview with '.$interview['interviewee']['name']['preferred'], null,
                    Meta::withText('Interview',
                        new Date(DateTimeImmutable::createFromFormat(DATE_ATOM, $interview['published'])))
                );
            });

        $arguments['leadParas'] = $arguments['interview']
            ->then(function (Result $interview) {
                return new LeadParas([new LeadPara($interview['impactStatement'])]);
            })
            ->otherwise(function () {
                return null;
            });

        return new Response($this->get('templating')->render('::interview.html.twig', $arguments));
    }
}
