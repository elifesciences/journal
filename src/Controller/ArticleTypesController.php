<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\Journal\Helper\ArticleType;
use eLife\Journal\Helper\Paginator;
use eLife\Journal\Pagerfanta\SequenceAdapter;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use eLife\Patterns\ViewModel\ContentHeaderSimple;
use eLife\Patterns\ViewModel\Link;
use eLife\Patterns\ViewModel\ListHeading;
use eLife\Patterns\ViewModel\ListingTeasers;
use eLife\Patterns\ViewModel\LoadMore;
use eLife\Patterns\ViewModel\Pager;
use eLife\Patterns\ViewModel\Teaser;
use InvalidArgumentException;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use function GuzzleHttp\Promise\all;
use function GuzzleHttp\Promise\promise_for;

final class ArticleTypesController extends Controller
{
    public function listAction(Request $request, string $type) : Response
    {
        $page = (int) $request->query->get('page', 1);
        $perPage = 6;

        $arguments = $this->defaultPageArguments();

        try {
            $arguments['type'] = ArticleType::plural($type);
        } catch (InvalidArgumentException $e) {
            throw new NotFoundHttpException('Unknown type '.$type, $e);
        }

        $latest = promise_for($this->get('elife.api_sdk.search')
            ->forType($type)
            ->sortBy('date'))
            ->then(function (Sequence $sequence) use ($page, $perPage) {
                $pagerfanta = new Pagerfanta(new SequenceAdapter($sequence));
                $pagerfanta->setMaxPerPage($perPage)->setCurrentPage($page);

                return $pagerfanta;
            });

        $arguments['paginator'] = $latest
            ->then(function (Pagerfanta $pagerfanta) use ($request) {
                return new Paginator($pagerfanta, function (int $page = null) use ($request) {
                    $routeParams = $request->attributes->get('_route_params');
                    $routeParams['page'] = $page;

                    return $this->get('router')->generate('article-type', $routeParams);
                });
            });

        $arguments['latest'] = $latest
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
        $arguments['contentHeader'] = ContentHeaderNonArticle::basic($arguments['type']);

        $arguments['latestHeading'] = new ListHeading($latestHeading = 'Latest articles');
        $arguments['latest'] = all(['latest' => $arguments['latest'], 'paginator' => $arguments['paginator']])
            ->then(function (array $parts) use ($latestHeading) {
                $latest = $parts['latest'];
                $paginator = $parts['paginator'];

                if ($latest->isEmpty()) {
                    return null;
                }

                $teasers = $latest->map($this->willConvertTo(Teaser::class))->toArray();

                if ($paginator->getNextPage()) {
                    return ListingTeasers::withPagination(
                        $teasers,
                        $paginator->getNextPage() ? new LoadMore(new Link('Load more', $paginator->getNextPagePath())) : null,
                        $latestHeading
                    );
                }

                return ListingTeasers::basic($teasers, $latestHeading);
            });

        return new Response($this->get('templating')->render('::article-type.html.twig', $arguments));
    }

    private function createSubsequentPage(array $arguments) : Response
    {
        $arguments['contentHeader'] = $arguments['paginator']
            ->then(function (Paginator $paginator) use ($arguments) {
                return new ContentHeaderSimple(
                    'Browse our '.lcfirst($arguments['type']),
                    sprintf('Page %s of %s', number_format($paginator->getCurrentPage()), number_format(count($paginator)))
                );
            });

        $arguments['latest'] = all(['latest' => $arguments['latest'], 'paginator' => $arguments['paginator']])
            ->then(function (array $parts) {
                $latest = $parts['latest'];
                $paginator = $parts['paginator'];

                $teasers = $latest->map($this->willConvertTo(Teaser::class))->toArray();

                return ListingTeasers::withPagination(
                    $teasers,
                    new Pager(
                        $paginator->getPreviousPage() ? new Link('Newer articles', $paginator->getPreviousPagePath()) : null,
                        $paginator->getNextPage() ? new Link('Older articles', $paginator->getNextPagePath()) : null
                    )
                );
            });

        return new Response($this->get('templating')->render('::article-type-alt.html.twig', $arguments));
    }
}
