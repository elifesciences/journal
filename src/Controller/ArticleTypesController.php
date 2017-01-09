<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Collection\Sequence;
use eLife\Journal\Helper\ArticleType;
use eLife\Journal\Helper\Paginator;
use eLife\Journal\Pagerfanta\SequenceAdapter;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use eLife\Patterns\ViewModel\ContentHeaderSimple;
use eLife\Patterns\ViewModel\ListingTeasers;
use eLife\Patterns\ViewModel\Teaser;
use InvalidArgumentException;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use function GuzzleHttp\Promise\promise_for;

final class ArticleTypesController extends Controller
{
    public function listAction(Request $request, string $type) : Response
    {
        $page = (int) $request->query->get('page', 1);
        $perPage = 6;

        $arguments = $this->defaultPageArguments();

        try {
            $arguments['title'] = ArticleType::plural($type);
        } catch (InvalidArgumentException $e) {
            throw new NotFoundHttpException('Unknown type '.$type, $e);
        }

        $latest = promise_for($this->get('elife.api_sdk.search')
            ->forType($type)
            ->sortBy('date'))
            ->then(function (Sequence $sequence) use ($page, $perPage) {
                $pagerfanta = new Pagerfanta(new SequenceAdapter($sequence, $this->willConvertTo(Teaser::class)));
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

        $arguments['listing'] = $arguments['paginator']
            ->then($this->willConvertTo(ListingTeasers::class, ['type' => 'articles']));

        if (1 === $page) {
            return $this->createFirstPage($arguments);
        }

        return $this->createSubsequentPage($arguments);
    }

    private function createFirstPage(array $arguments) : Response
    {
        $arguments['contentHeader'] = ContentHeaderNonArticle::basic($arguments['title']);

        return new Response($this->get('templating')->render('::article-type.html.twig', $arguments));
    }

    private function createSubsequentPage(array $arguments) : Response
    {
        $arguments['contentHeader'] = $arguments['paginator']
            ->then(function (Paginator $paginator) use ($arguments) {
                return new ContentHeaderSimple(
                    'Browse our '.lcfirst($arguments['title']),
                    sprintf('Page %s of %s', number_format($paginator->getCurrentPage()), number_format(count($paginator)))
                );
            });

        return new Response($this->get('templating')->render('::pagination.html.twig', $arguments));
    }
}
