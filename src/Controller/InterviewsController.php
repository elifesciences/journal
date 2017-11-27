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
use eLife\Patterns\ViewModel\ContextualDataMetric;
use eLife\Patterns\ViewModel\Listing;
use eLife\Patterns\ViewModel\ListingTeasers;
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
        $interview = $this->get('elife.api_sdk.interviews')
            ->get($id)
            ->otherwise($this->mightNotExist())
            ->then($this->checkSlug($request, function (Interview $interview) {
                return $interview->getInterviewee()->getPerson()->getPreferredName();
            }));

        $arguments = $this->defaultPageArguments($request, $interview);

        $arguments['title'] = $interview
            ->then(Callback::method('getTitle'));

        $arguments['interview'] = $interview;

        $arguments['contentHeader'] = $arguments['interview']
            ->then($this->willConvertTo(ContentHeader::class));

        $arguments['contextualData'] = $arguments['interview']
            ->then($this->ifGranted(['FEATURE_CAN_USE_HYPOTHESIS'], function (Interview $interview) {
                $metrics = [new ContextualDataMetric('Annotations', 0, 'annotation-count')];

                return ContextualData::withMetrics($metrics);
            }));

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

        $arguments['collections'] = $this->get('elife.api_sdk.collections')
            ->containing(Identifier::interview($id))
            ->slice(0, 10)
            ->map($this->willConvertTo(Teaser::class, ['variant' => 'relatedItem', 'from' => 'interview', 'unrelated' => false]))
            ->then(Callback::emptyOr(function (Sequence $collections) {
                return ListingTeasers::basic($collections->toArray());
            }))
            ->otherwise($this->softFailure("Failed to load collections for interview '$id'"));

        return new Response($this->get('templating')->render('::interview.html.twig', $arguments));
    }
}
