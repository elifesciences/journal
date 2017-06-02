<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Subject;
use eLife\Journal\Helper\Callback;
use eLife\Journal\Helper\CreatesIiifUri;
use eLife\Journal\Helper\Paginator;
use eLife\Journal\Pagerfanta\SequenceAdapter;
use eLife\Journal\ViewModel\EmptyListing;
use eLife\Patterns\ViewModel\BlockLink;
use eLife\Patterns\ViewModel\ContentHeader;
use eLife\Patterns\ViewModel\ContentHeaderSimple;
use eLife\Patterns\ViewModel\GridListing;
use eLife\Patterns\ViewModel\Image;
use eLife\Patterns\ViewModel\Link;
use eLife\Patterns\ViewModel\ListHeading;
use eLife\Patterns\ViewModel\ListingTeasers;
use eLife\Patterns\ViewModel\Picture;
use eLife\Patterns\ViewModel\Teaser;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function GuzzleHttp\Promise\all;
use function GuzzleHttp\Promise\promise_for;

final class SubjectsController extends Controller
{
    use CreatesIiifUri;

    public function listAction(Request $request) : Response
    {
        $arguments = $this->defaultPageArguments($request);

        $arguments['title'] = 'Research categories';

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
                    new Picture(
                        [
                            [
                                'srcset' => sprintf('%s 263w, %s 526w', $this->iiifUri($subject->getThumbnail(), 263, 148), $this->iiifUri($subject->getThumbnail(), 526, 296)),
                                'media' => '(min-width: 600px)',
                            ],
                        ],
                        new Image('data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==')
                    )
                );
            })
            ->then(function (Sequence $subjects) {
                if ($subjects->isEmpty()) {
                    return new EmptyListing(null, 'No subjects available.');
                }

                return GridListing::forBlockLinks($subjects->toArray());
            });

        return new Response($this->get('templating')->render('::subjects.html.twig', $arguments));
    }

    public function subjectAction(Request $request, string $id) : Response
    {
        $page = (int) $request->query->get('page', 1);
        $perPage = 10;

        $subject = $this->get('elife.api_sdk.subjects')
            ->get($id)
            ->otherwise($this->mightNotExist());

        $arguments = $this->defaultPageArguments($request, $subject);

        $arguments['id'] = $id;
        $arguments['subject'] = $subject;

        $latestArticles = promise_for($this->get('elife.api_sdk.search')
            ->forSubject($id)
            ->forType('research-article', 'research-advance', 'scientific-correspondence', 'short-report', 'tools-resources', 'replication-study', 'editorial', 'insight', 'feature', 'collection')
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
            ->then($this->willConvertTo(ContentHeader::class));

        $arguments['highlights'] = (new PromiseSequence($this->get('elife.api_sdk.highlights')
            ->get($arguments['id'])
            ->slice(0, 3)))
            ->map($this->willConvertTo(Teaser::class, ['variant' => 'secondary']))
            ->then(Callback::emptyOr(function (Sequence $result) {
                return ListingTeasers::basic($result->toArray(), new ListHeading('Highlights'));
            }))
            ->otherwise($this->softFailure('Failed to load highlights for '.$arguments['id']));

        return new Response($this->get('templating')->render('::subject.html.twig', $arguments));
    }
}
