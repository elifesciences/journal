<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\ArticleVersion;
use eLife\Patterns\ViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use function eLife\Patterns\mixed_visibility_text;

final class ArticleDownloadLinksListConverter implements ViewModelConverter
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param ArticleVersion $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $groups = [];

        if ($object->getPdf()) {
            $groups[mixed_visibility_text('', 'Downloads', '(link to download the article as PDF)')] = [new ViewModel\Link('Article', $object->getPdf())];
        }

        $articleUri = $this->urlGenerator->generate('article', ['volume' => $object->getVolume(), 'id' => $object->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        $groups[mixed_visibility_text('', 'Open citations', '(links to open the citations from this article in various online reference manager services)')] = [
            new ViewModel\Link('Mendeley', 'https://www.mendeley.com/import?doi='.$object->getDoi()),
            new ViewModel\Link('ReadCube', 'https://www.readcube.com/articles/'.$object->getDoi()),
            new ViewModel\Link('Papers', sprintf('papers2://url/%s?title=%s', urlencode($articleUri), urlencode(strip_tags($object->getTitle())))),
            new ViewModel\Link('CiteULike', sprintf('http://www.citeulike.org/posturl?url=%s&title=%s&doi=%s', urlencode($articleUri), urlencode(strip_tags($object->getTitle())), $object->getDoi())),
        ];

        return new ViewModel\ArticleDownloadLinksList('downloads', 'A two-part list of links to download the article, or parts of the article, in various formats.', $groups);
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof ArticleVersion && ViewModel\ArticleDownloadLinksList::class === $viewModel;
    }
}
