<?php

namespace eLife\Journal\Controller;

use eLife\Journal\Helper\Callback;
use eLife\Journal\Helper\HasPages;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use eLife\Patterns\ViewModel\ListingTeasers;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class PressPacksController extends Controller
{
    use HasPages;

    public function listAction(Request $request) : Response
    {
        $page = (int) $request->query->get('page', 1);
        $perPage = 6;

        $arguments = $this->defaultPageArguments();

        $latest = $this->pagerfantaPromise(
            $this->get('elife.api_sdk.press_packages'),
            $page,
            $perPage
        );

        $arguments['title'] = 'For the press';

        $arguments['paginator'] = $this->paginator(
            $latest,
            $request,
            'Browse our press packs',
            'press-packs'
        );

        $arguments['listing'] = $arguments['paginator']
            ->then($this->willConvertTo(ListingTeasers::class, ['emptyText' => 'No press packs available.']));

        if (1 === $page) {
            return $this->createFirstPage($arguments);
        }

        return $this->createSubsequentPage($request, $arguments);
    }

    private function createFirstPage(array $arguments) : Response
    {
        $arguments['contentHeader'] = ContentHeaderNonArticle::basic($arguments['title']);

        return new Response($this->get('templating')->render('::press-packs.html.twig', $arguments));
    }

    public function pressPackAction(Request $request, string $id) : Response
    {
        $package = $this->get('elife.api_sdk.press_packages')
            ->get($id)
            ->otherwise($this->mightNotExist())
            ->then($this->checkSlug($request, Callback::method('getTitle')));

        $arguments = $this->defaultPageArguments($package);

        $arguments['package'] = $package;

        $arguments['contentHeader'] = $arguments['package']
            ->then($this->willConvertTo(ContentHeaderNonArticle::class));

        return new Response($this->get('templating')->render('::press-pack.html.twig', $arguments));
    }
}
