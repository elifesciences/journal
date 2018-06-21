<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Identifier;
use eLife\ApiSdk\Model\Interview;
use eLife\ApiSdk\Model\IntervieweeCvLine;
use eLife\Journal\Helper\Callback;
use eLife\Journal\Helper\Paginator;
use eLife\Journal\Pagerfanta\SequenceAdapter;
use eLife\Patterns\ViewModel\ArticleSection;
use eLife\Patterns\ViewModel\ContentHeader;
use eLife\Patterns\ViewModel\ContextualData;
use eLife\Patterns\ViewModel\Listing;
use eLife\Patterns\ViewModel\ListingTeasers;
use eLife\Patterns\ViewModel\SpeechBubble;
use eLife\Patterns\ViewModel\Teaser;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function GuzzleHttp\Promise\promise_for;

final class InterviewsController extends Controller
{
    public function listAction(Request $request) : Response
    {
        $page = (int) $request->query->get('page', 1);
        $perPage = 10;

        $arguments = $this->defaultPageArguments($request);

        $latest = promise_for($this->get('elife.api_sdk.interviews'))
            ->then(function (Sequence $sequence) use ($page, $perPage) {
                $pagerfanta = new Pagerfanta(new SequenceAdapter($sequence, $this->willConvertTo(Teaser::class)));
                $pagerfanta->setMaxPerPage($perPage)->setCurrentPage($page);

                return $pagerfanta;
            });

        $arguments['title'] = 'Interviews';

        $arguments['paginator'] = $latest
            ->then(function (Pagerfanta $pagerfanta) use ($request) {
                return new Paginator(
                    'Browse our interviews',
                    $pagerfanta,
                    function (int $page = null) use ($request) {
                        $routeParams = $request->attributes->get('_route_params');
                        $routeParams['page'] = $page;

                        return $this->get('router')->generate('interviews', $routeParams);
                    }
                );
            });

        $arguments['listing'] = $arguments['paginator']
            ->then($this->willConvertTo(ListingTeasers::class, ['heading' => 'Latest', 'emptyText' => 'No interviews available.']));

        if (1 === $page) {
            return $this->createFirstPage($arguments);
        }

        return $this->createSubsequentPage($request, $arguments);
    }

    private function createFirstPage(array $arguments) : Response
    {
        $arguments['contentHeader'] = new ContentHeader($arguments['title']);

        return new Response($this->get('templating')->render('::interviews.html.twig', $arguments));
    }

    public function interviewAction(Request $request, string $id) : Response
    {
        $arguments['item'] = $this->get('elife.api_sdk.interviews')
            ->get($id)
            ->otherwise($this->mightNotExist())
            ->then($this->checkSlug($request, function (Interview $interview) {
                return $interview->getInterviewee()->getPerson()->getPreferredName();
            }));

        $arguments = $this->defaultPageArguments($request, $arguments['item']);

        $arguments['title'] = $arguments['item']
            ->then(Callback::method('getTitle'));

        $arguments['contentHeader'] = $arguments['item']
            ->then($this->willConvertTo(ContentHeader::class));

        $arguments['pageViews'] = $this->get('elife.api_sdk.metrics')
            ->totalPageViews(Identifier::interview($id))
            ->otherwise($this->mightNotExist())
            ->otherwise($this->softFailure('Failed to load page views count'));

        $arguments['contextualData'] = $arguments['pageViews']
            ->then(Callback::emptyOr(function (int $pageViews) {
                return ContextualData::withMetrics([sprintf('Views %s', number_format($pageViews))], null, null, SpeechBubble::forContextualData());
            }, function () {
                return ContextualData::annotationsOnly(SpeechBubble::forContextualData());
            }));

        $arguments['blocks'] = $arguments['item']
            ->then($this->willConvertContent())
            ->then(function (Sequence $blocks) {
                return $blocks->prepend(SpeechBubble::forArticleBody());
            });

        $arguments['cv'] = $arguments['item']
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

        $arguments['collections'] = $this->get('elife.api_sdk.collections')
            ->containing(Identifier::interview($id))
            ->slice(0, 10)
            ->map($this->willConvertTo(Teaser::class, ['variant' => 'relatedItem', 'from' => 'interview', 'related' => true]))
            ->then(Callback::emptyOr(function (Sequence $collections) {
                return ListingTeasers::basic($collections->toArray());
            }))
            ->otherwise($this->softFailure("Failed to load collections for interview '$id'"));

        return new Response($this->get('templating')->render('::interview.html.twig', $arguments));
    }
}
