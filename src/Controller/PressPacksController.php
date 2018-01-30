<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Model\PressPackage;
use eLife\Journal\Helper\Callback;
use eLife\Journal\Helper\HasPages;
use eLife\Patterns\ViewModel\ArticleSection;
use eLife\Patterns\ViewModel\ContentHeader;
use eLife\Patterns\ViewModel\ContextualData;
use eLife\Patterns\ViewModel\Listing;
use eLife\Patterns\ViewModel\ListingTeasers;
use eLife\Patterns\ViewModel\Paragraph;
use eLife\Patterns\ViewModel\SpeechBubble;
use eLife\Patterns\ViewModel\Teaser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class PressPacksController extends Controller
{
    use HasPages;

    public function listAction(Request $request) : Response
    {
        $page = (int) $request->query->get('page', 1);
        $perPage = 10;

        $arguments = $this->defaultPageArguments($request);

        $latest = $this->pagerfantaPromise(
            $this->get('elife.api_sdk.press_packages'),
            $page,
            $perPage,
            $this->willConvertTo(Teaser::class)
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
        $arguments['contentHeader'] = new ContentHeader($arguments['title']);

        $arguments['sidebar'] = [
            new Paragraph('Enquiries about published papers, material in press and eLife may be directed to <a href="mailto:press@elifesciences.org">press@elifesciences.org</a>, or +44&nbsp;1223&nbsp;855373.'),
            new Paragraph('If you’re writing about an eLife study, please cite eLife as the source of the article and include a link to either the article or elifesciences.org, preferably using our DOI: https://doi.org/10.7554/eLife – with the article’s five-digit extension (e.g. https://doi.org/10.7554/eLife.00000). Thank you!'),
            new Paragraph('All content, unless otherwise stated, is available under a <a href="https://creativecommons.org/licenses/by/4.0">Creative Commons Attribution License (CC-BY)</a>. All are free to use and reuse the content provided the original source and authors are credited.'),
            new Paragraph('The eLife media policy, encouraging authors to present and discuss their works ahead of publication and indicating that eLife will not release content under embargo, can be found under the Media Policy heading in our <a href="https://submit.elifesciences.org/html/elife_author_instructions.html#policies">Journal Policies</a>.'),
            new Paragraph('Please don’t hesitate to contact us if you have any questions.'),
        ];

        return new Response($this->get('templating')->render('::press-packs.html.twig', $arguments));
    }

    public function pressPackAction(Request $request, string $id) : Response
    {
        $item = $this->get('elife.api_sdk.press_packages')
            ->get($id)
            ->otherwise($this->mightNotExist())
            ->then($this->checkSlug($request, Callback::method('getTitle')));

        $arguments = $this->defaultPageArguments($request, $item);

        $arguments['title'] = $arguments['item']
            ->then(Callback::method('getTitle'));

        $arguments['contentHeader'] = $arguments['item']
            ->then($this->willConvertTo(ContentHeader::class));

        $arguments['contextualData'] = $arguments['item']
            ->then($this->ifGranted(['FEATURE_CAN_USE_HYPOTHESIS'], function (PressPackage $package) {
                return ContextualData::annotationsOnly(SpeechBubble::forContextualData());
            }));

        $arguments['blocks'] = $arguments['item']
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

        if ($this->isGranted('FEATURE_CAN_USE_HYPOTHESIS')) {
            $arguments['speechBubble'] = SpeechBubble::forArticleBody();
        }

        $arguments['relatedContent'] = $arguments['item']
            ->then(Callback::methodEmptyOr('getRelatedContent', function (PressPackage $package) {
                return ListingTeasers::basic($package->getRelatedContent()->map($this->willConvertTo(Teaser::class, ['variant' => 'secondary']))->toArray());
            }));

        return new Response($this->get('templating')->render('::press-pack.html.twig', $arguments));
    }
}
