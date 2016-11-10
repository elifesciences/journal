<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Interview;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use eLife\Patterns\ViewModel\LeadPara;
use eLife\Patterns\ViewModel\LeadParas;
use Symfony\Component\HttpFoundation\Response;

final class InterviewsController extends Controller
{
    public function interviewAction(string $id) : Response
    {
        $arguments = $this->defaultPageArguments();

        $arguments['interview'] = $this->get('elife.api_sdk.interviews')->get($id);

        $arguments['contentHeader'] = $arguments['interview']
            ->then(function (Interview $interview) {
                return $this->get('elife.journal.view_model.converter')->convert($interview, ContentHeaderNonArticle::class);
            });

        $arguments['leadParas'] = $arguments['interview']
            ->then(function (Interview $interview) {
                return new LeadParas([new LeadPara($interview->getImpactStatement())]);
            })
            ->otherwise(function () {
                return null;
            });

        $arguments['blocks'] = $arguments['interview']
            ->then(function (Interview $interview) {
                return $interview->getContent()->map(function (Block $block) {
                    return $this->get('elife.journal.view_model.converter')->convert($block);
                });
            });

        return new Response($this->get('templating')->render('::interview.html.twig', $arguments));
    }
}
