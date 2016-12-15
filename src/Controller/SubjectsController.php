<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\Subject;
use eLife\Journal\Helper\Paginator;
use eLife\Journal\Pagerfanta\SequenceAdapter;
use eLife\Patterns\ViewModel\BackgroundImage;
use eLife\Patterns\ViewModel\BlockLink;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use eLife\Patterns\ViewModel\ContentHeaderSimple;
use eLife\Patterns\ViewModel\GridListing;
use eLife\Patterns\ViewModel\LeadPara;
use eLife\Patterns\ViewModel\LeadParas;
use eLife\Patterns\ViewModel\Link;
use eLife\Patterns\ViewModel\ListHeading;
use eLife\Patterns\ViewModel\ListingTeasers;
use eLife\Patterns\ViewModel\LoadMore;
use eLife\Patterns\ViewModel\Pager;
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

        $arguments['contentHeader'] = ContentHeaderNonArticle::basic('Browse our research categories');

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
            ->then(function (Sequence $subjects) {
                if ($subjects->isEmpty()) {
                    return null;
                }

                return GridListing::forBlockLinks($subjects->toArray());
            });

        return new Response($this->get('templating')->render('::subjects.html.twig', $arguments));
    }

    public function subjectAction(Request $request, string $id) : Response
    {
        $page = (int) $request->query->get('page', 1);
        $perPage = 6;

        $arguments = $this->defaultPageArguments();

        $arguments['subject'] = $this->get('elife.api_sdk.subjects')->get($id);

        $latestArticles = promise_for($this->get('elife.api_sdk.search')
            ->forSubject($id)
            ->forType('research-article', 'research-advance', 'research-exchange', 'short-report', 'tools-resources', 'replication-study', 'editorial', 'insight', 'feature', 'collection')
            ->sortBy('date'))
            ->then(function (Sequence $sequence) use ($page, $perPage) {
                $pagerfanta = new Pagerfanta(new SequenceAdapter($sequence));
                $pagerfanta->setMaxPerPage($perPage)->setCurrentPage($page);

                return $pagerfanta;
            });

        $arguments['paginator'] = $latestArticles
            ->then(function (Pagerfanta $pagerfanta) use ($request) {
                return new Paginator($pagerfanta, function (int $page = null) use ($request) {
                    $routeParams = $request->attributes->get('_route_params');
                    $routeParams['page'] = $page;

                    return $this->get('router')->generate('subject', $routeParams);
                });
            });

        $arguments['latestArticles'] = $latestArticles
            ->then(function (Pagerfanta $pagerfanta) {
                return new ArraySequence(iterator_to_array($pagerfanta));
            });

        if (1 === $page) {
            return $this->createFirstPage($arguments);
        }

        return $this->createSubsequentPage($arguments);
    }

    private function createFirstPage(array $arguments) : Response
    {
        $arguments['contentHeader'] = $arguments['subject']
            ->then(function (Subject $subject) {
                return $this->get('elife.journal.view_model.converter')->convert($subject, ContentHeaderNonArticle::class);
            });

        $arguments['lead_paras'] = $arguments['subject']
            ->then(function (Subject $subject) {
                return new LeadParas([new LeadPara($subject->getImpactStatement())]);
            })
            ->otherwise(function () {
                return null;
            });

        $arguments['latestArticlesHeading'] = new ListHeading($latestArticlesHeading = 'Latest articles');
        $arguments['latestArticles'] = all(['latestArticles' => $arguments['latestArticles'], 'paginator' => $arguments['paginator']])
            ->then(function (array $parts) use ($latestArticlesHeading) {
                $latestArticles = $parts['latestArticles'];
                $paginator = $parts['paginator'];

                if ($latestArticles->isEmpty()) {
                    return null;
                }

                $teasers = $latestArticles->map(function (Model $model) {
                    return $this->get('elife.journal.view_model.converter')->convert($model, Teaser::class);
                })->toArray();

                if ($paginator->getNextPage()) {
                    return ListingTeasers::withPagination(
                        $teasers,
                        $paginator->getNextPage() ? new LoadMore(new Link('Load more articles', $paginator->getNextPagePath())) : null,
                        $latestArticlesHeading
                    );
                }

                return ListingTeasers::basic($teasers, $latestArticlesHeading);
            });

        return new Response($this->get('templating')->render('::subject.html.twig', $arguments));
    }

    private function createSubsequentPage(array $arguments) : Response
    {
        $arguments['contentHeader'] = all(['subject' => $arguments['subject'], 'paginator' => $arguments['paginator']])
            ->then(function (array $parts) {
                $subject = $parts['subject'];
                $paginator = $parts['paginator'];

                return new ContentHeaderSimple(
                    sprintf('Browse our latest %s articles', $subject->getName()),
                    sprintf('Page %s of %s', number_format($paginator->getCurrentPage()), number_format(count($paginator)))
                );
            });

        $arguments['latestArticles'] = all(['latestArticles' => $arguments['latestArticles'], 'paginator' => $arguments['paginator']])
            ->then(function (array $parts) {
                $latestArticles = $parts['latestArticles'];
                $paginator = $parts['paginator'];

                return ListingTeasers::withPagination(
                    $latestArticles->map(function (Model $model) {
                        return $this->get('elife.journal.view_model.converter')->convert($model, Teaser::class);
                    })->toArray(),
                    new Pager(
                        $paginator->getPreviousPage() ? new Link('Newer articles', $paginator->getPreviousPagePath()) : null,
                        $paginator->getNextPage() ? new Link('Older articles', $paginator->getNextPagePath()) : null
                    )
                );
            });

        return new Response($this->get('templating')->render('::subject-alt.html.twig', $arguments));
    }
}
