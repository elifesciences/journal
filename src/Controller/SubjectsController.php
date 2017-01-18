<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Subject;
use eLife\Journal\Helper\Callback;
use eLife\Journal\Helper\Paginator;
use eLife\Journal\Pagerfanta\SequenceAdapter;
use eLife\Patterns\ViewModel\BackgroundImage;
use eLife\Patterns\ViewModel\BlockLink;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use eLife\Patterns\ViewModel\ContentHeaderSimple;
use eLife\Patterns\ViewModel\GridListing;
use eLife\Patterns\ViewModel\LeadParas;
use eLife\Patterns\ViewModel\Link;
use eLife\Patterns\ViewModel\ListingTeasers;
use eLife\Patterns\ViewModel\Teaser;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function GuzzleHttp\Promise\all;
use function GuzzleHttp\Promise\promise_for;

final class SubjectsController extends Controller
{
    public function listAction() : Response
    {
        $arguments = $this->defaultPageArguments();

        $arguments['contentHeader'] = new ContentHeaderSimple('Browse our research categories');

        $arguments['subjects'] = $this->get('elife.api_sdk.subjects')
            ->reverse()
            ->slice(1, 100)
            ->map(function (Subject $subject) {
                return new BlockLink(
                    new Link(
                        $subject->getName(),
                        $this->get('router')->generate('subject', ['id' => $subject->getId()])
                    ),
                    new BackgroundImage(
                        $subject->getThumbnail()->getSize('16:9')->getImage(250),
                        $subject->getThumbnail()->getSize('16:9')->getImage(500),
                        600
                    )
                );
            })
            ->then(Callback::emptyOr(function (Sequence $subjects) {
                return GridListing::forBlockLinks($subjects->toArray());
            }));

        return new Response($this->get('templating')->render('::subjects.html.twig', $arguments));
    }

    public function subjectAction(Request $request, string $id) : Response
    {
        $page = (int) $request->query->get('page', 1);
        $perPage = 6;

        $subject = $this->get('elife.api_sdk.subjects')->get($id);

        $arguments = $this->defaultPageArguments($subject);

        $arguments['subject'] = $subject;

        $latestArticles = promise_for($this->get('elife.api_sdk.search')
            ->forSubject($id)
            ->forType('research-article', 'research-advance', 'research-exchange', 'short-report', 'tools-resources', 'replication-study', 'editorial', 'insight', 'feature', 'collection')
            ->sortBy('date'))
            ->then(function (Sequence $sequence) use ($page, $perPage) {
                $pagerfanta = new Pagerfanta(new SequenceAdapter($sequence, $this->willConvertTo(Teaser::class)));
                $pagerfanta->setMaxPerPage($perPage)->setCurrentPage($page);

                return $pagerfanta;
            });

        $arguments['title'] = $arguments['subject']
            ->then(Callback::method('getName'));

        $arguments['paginator'] = all(['subject' => $arguments['subject'], 'latestArticles' => $latestArticles])
            ->then(function (array $parts) use ($request) {
                $subject = $parts['subject'];
                $latestArticles = $parts['latestArticles'];

                return new Paginator(
                    sprintf('Browse our latest %s articles', $subject->getName()),
                    $latestArticles,
                    function (int $page = null) use ($request) {
                        $routeParams = $request->attributes->get('_route_params');
                        $routeParams['page'] = $page;

                        return $this->get('router')->generate('subject', $routeParams);
                    }
                );
            });

        $arguments['listing'] = $arguments['paginator']
            ->then($this->willConvertTo(ListingTeasers::class, ['type' => 'articles']));

        if (1 === $page) {
            return $this->createFirstPage($arguments);
        }

        return $this->createSubsequentPage($request, $arguments);
    }

    private function createFirstPage(array $arguments) : Response
    {
        $arguments['contentHeader'] = $arguments['subject']
            ->then($this->willConvertTo(ContentHeaderNonArticle::class));

        $arguments['lead_paras'] = $arguments['subject']
            ->then(Callback::methodEmptyOr('getImpactStatement', $this->willConvertTo(LeadParas::class)));

        return new Response($this->get('templating')->render('::subject.html.twig', $arguments));
    }
}
