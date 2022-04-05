<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\ArticleVoR;
use eLife\Journal\Helper\CanConvertContent;
use eLife\Journal\Helper\DownloadLink;
use eLife\Journal\Helper\DownloadLinkUriGenerator;
use eLife\Journal\Helper\Humanizer;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use function eLife\Patterns\mixed_visibility_text;

final class ArticleDownloadLinksListConverter implements ViewModelConverter
{
    use CanConvertContent;

    private $viewModelConverter;
    private $patternRenderer;
    private $urlGenerator;
    private $downloadLinkUriGenerator;

    public function __construct(ViewModelConverter $viewModelConverter, PatternRenderer $patternRenderer, UrlGeneratorInterface $urlGenerator, DownloadLinkUriGenerator $downloadLinkUriGenerator)
    {
        $this->viewModelConverter = $viewModelConverter;
        $this->patternRenderer = $patternRenderer;
        $this->urlGenerator = $urlGenerator;
        $this->downloadLinkUriGenerator = $downloadLinkUriGenerator;
    }

    /**
     * @param ArticleVersion $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $groups = [];

        $downloads = [];
        $types = [];

        $articleUri = $this->urlGenerator->generate('article', [$object], UrlGeneratorInterface::ABSOLUTE_URL);

        if ($object->getPdf()) {
            $types[] = 'PDF';
            $downloads[] = new ViewModel\ArticleDownloadLink(new ViewModel\Link(
                'Article PDF',
                $this->downloadLinkUriGenerator->generate(DownloadLink::fromUri($object->getPdf().'?'.DownloadLink::QUERY_PARAMETER_CANONICAL_URI.'='.$articleUri)),
                false,
                ['article-identifier' => $object->getDoi(), 'download-type' => 'pdf-article']
            ));

            if ($object instanceof ArticleVor && $object->getFiguresPdf()) {
                $downloads[] = new ViewModel\ArticleDownloadLink(new ViewModel\Link(
                    'Figures PDF',
                    $this->downloadLinkUriGenerator->generate(DownloadLink::fromUri($object->getFiguresPdf())),
                    false,
                    ['article-identifier' => $object->getDoi(), 'download-type' => 'pdf-figures']
                ));
            }
        }

        if (!empty($context['era-download'])) {
            $types[] = 'Executable version';
            $downloads[] = new ViewModel\ArticleDownloadLink(
                new ViewModel\Link(
                    'Executable version',
                    $this->urlGenerator->generate('article-era-download', [$object], UrlGeneratorInterface::ABSOLUTE_URL),
                    false,
                    ['article-identifier' => $object->getDoi(), 'download-type' => 'era-download']
                ),
                new ViewModel\Link(
                    'What are executable versions?',
                    $this->urlGenerator->generate('labs-post', ['id' => 'dc5acbde'])
                )
            );
        }

        if ($downloads) {
            $groups[] = new ViewModel\ArticleDownloadLinksGroup(
                mixed_visibility_text('', 'Downloads', '(link to download the article as '.Humanizer::prettyList(...$types).')'),
                $downloads
            );
        }

        $groups[] = new ViewModel\ArticleDownloadLinksGroup(
            mixed_visibility_text('', 'Open citations', '(links to open the citations from this article in various online reference manager services)'),
            [
                new ViewModel\ArticleDownloadLink(new ViewModel\Link('Mendeley', 'https://www.mendeley.com/import?doi='.$object->getDoi())),
                new ViewModel\ArticleDownloadLink(new ViewModel\Link('ReadCube', 'https://www.readcube.com/articles/'.$object->getDoi())),
            ]
        );

        if ($object->getPublishedDate()) {
            $groups[] = new ViewModel\ArticleDownloadLinksGroup(
                mixed_visibility_text('', 'Cite this article', '(links to download the citations from this article in formats compatible with various reference manager tools)'),
                [
                    new ViewModel\ArticleDownloadLink(new ViewModel\Link('BibTeX', $this->urlGenerator->generate('article-bibtex', [$object]))),
                    new ViewModel\ArticleDownloadLink(new ViewModel\Link('RIS', $this->urlGenerator->generate('article-ris', [$object]))),
                ],
                $this->patternRenderer->render($this->convertTo($object, ViewModel\Reference::class)),
                'cite-this-article',
                true
            );
        }

        return new ViewModel\ArticleDownloadLinksList('downloads', 'A two-part list of links to download the article, or parts of the article, in various formats.', $groups);
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof ArticleVersion && ViewModel\ArticleDownloadLinksList::class === $viewModel;
    }

    protected function getViewModelConverter() : ViewModelConverter
    {
        return $this->viewModelConverter;
    }
}
