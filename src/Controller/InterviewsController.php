<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Model\Interview;
use eLife\Journal\Helper\Callback;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use eLife\Patterns\ViewModel\LeadParas;
use Symfony\Component\HttpFoundation\Response;

final class InterviewsController extends Controller
{
    public function interviewAction(string $id) : Response
    {
        $interview = $this->get('elife.api_sdk.interviews')->get($id);

        $arguments = $this->defaultPageArguments($interview);

        $arguments['interview'] = $interview;

        $arguments['contentHeader'] = $arguments['interview']
            ->then($this->willConvertTo(ContentHeaderNonArticle::class));

        $arguments['leadParas'] = $arguments['interview']
            ->then(Callback::methodEmptyOr('getImpactStatement', $this->willConvertTo(LeadParas::class)));

        $arguments['blocks'] = $arguments['interview']
            ->then($this->willConvertContent());

        return new Response($this->get('templating')->render('::interview.html.twig', $arguments));
    }
}
