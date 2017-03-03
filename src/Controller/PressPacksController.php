<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Model\PressPackage;
use eLife\Journal\Helper\Callback;
use eLife\Journal\Helper\HasPages;
use eLife\Patterns\ViewModel\ArticleSection;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use eLife\Patterns\ViewModel\LeadParas;
use eLife\Patterns\ViewModel\Listing;
use eLife\Patterns\ViewModel\ListingTeasers;
use eLife\Patterns\ViewModel\Teaser;
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

        $arguments['title'] = $package
            ->then(Callback::method('getTitle'));

        $arguments['package'] = $package;

        $arguments['contentHeader'] = $arguments['package']
            ->then($this->willConvertTo(ContentHeaderNonArticle::class));

        $arguments['leadParas'] = $arguments['package']
            ->then(Callback::methodEmptyOr('getImpactStatement', $this->willConvertTo(LeadParas::class)));

        $arguments['blocks'] = $arguments['package']
            ->then(function (PressPackage $package) {
                $parts = $this->convertContent($package)->toArray();

                if ($package->getMediaContacts()->notEmpty()) {
                    $mediaContacts = Listing::ordered($package->getMediaContacts()->map($this->willConvertTo())->map($this->willRender())->toArray());

                    $parts[] = ArticleSection::basic('Media contacts', 2, $this->render($mediaContacts));
                }

                if ($package->getAbout()->notEmpty()) {
                    $parts[] = ArticleSection::basic('About', 2, $this->render(...$package->getAbout()->map($this->willConvertTo(null, ['level' => 2]))));
                }

                return $parts;
            });

        $arguments['relatedContent'] = $arguments['package']
            ->then(function (PressPackage $package) {
                return ListingTeasers::basic($package->getRelatedContent()->map($this->willConvertTo(Teaser::class, ['variant' => 'secondary']))->toArray());
            });

        return new Response($this->get('templating')->render('::press-pack.html.twig', $arguments));
    }
}
