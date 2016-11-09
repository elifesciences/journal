<?php

namespace eLife\Journal\Controller;

use DateTimeImmutable;
use eLife\ApiClient\ApiClient\InterviewsClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use eLife\Patterns\ViewModel\Date;
use eLife\Patterns\ViewModel\LeadPara;
use eLife\Patterns\ViewModel\LeadParas;
use eLife\Patterns\ViewModel\Meta;
use Symfony\Component\HttpFoundation\Response;

final class InterviewsController extends Controller
{
    public function interviewAction(string $id) : Response
    {
        $arguments = $this->defaultPageArguments();

        $arguments['interview'] = $this->get('elife.api_client.interviews')
            ->getInterview(['Accept' => new MediaType(InterviewsClient::TYPE_INTERVIEW, 1)], $id);

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

        $arguments['blocks'] = $arguments['interview']
            ->then(function (Result $interview) {
                return $this->get('elife.website.view_model.block_converter')->handleBlocks(...$interview['content']);
            });

        return new Response($this->get('templating')->render('::interview.html.twig', $arguments));
    }
}
