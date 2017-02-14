<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Model\Interview;
use eLife\ApiSdk\Model\IntervieweeCvLine;
use eLife\Journal\Helper\Callback;
use eLife\Patterns\ViewModel\ArticleSection;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use eLife\Patterns\ViewModel\LeadParas;
use eLife\Patterns\ViewModel\Listing;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class InterviewsController extends Controller
{
    public function interviewAction(Request $request, string $id) : Response
    {
        $interview = $this->get('elife.api_sdk.interviews')
            ->get($id)
            ->otherwise($this->mightNotExist())
            ->then($this->checkSlug($request, function (Interview $interview) {
                return $interview->getInterviewee()->getPerson()->getPreferredName();
            }));

        $arguments = $this->defaultPageArguments($interview);

        $arguments['interview'] = $interview;

        $arguments['contentHeader'] = $arguments['interview']
            ->then($this->willConvertTo(ContentHeaderNonArticle::class));

        $arguments['leadParas'] = $arguments['interview']
            ->then(Callback::methodEmptyOr('getImpactStatement', $this->willConvertTo(LeadParas::class)));

        $arguments['blocks'] = $arguments['interview']
            ->then($this->willConvertContent());

        $arguments['cv'] = $arguments['interview']
            ->then(function (Interview $interview) {
                if ($interview->getInterviewee()->getCvLines()->isEmpty()) {
                    return null;
                }

                $cv = Listing::ordered($interview->getInterviewee()->getCvLines()->map(function (IntervieweeCvLine $cvLine) {
                    return sprintf('<b>%s</b>: %s', $cvLine->getDate(), $cvLine->getText());
                })->toArray(), 'bullet');

                return ArticleSection::basic(
                    $interview->getInterviewee()->getPerson()->getPreferredName().' CV',
                    2,
                    $this->render($cv)
                );
            });

        return new Response($this->get('templating')->render('::interview.html.twig', $arguments));
    }
}
