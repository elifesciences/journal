<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\ArticleVersion;
use eLife\Journal\Helper\CanConvertContent;
use eLife\Journal\Helper\DownloadLinkUriGenerator;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\Button;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ContentAsideConverter implements ViewModelConverter
{
    use CanConvertContent;
    use CreatesDate;

    private $viewModelConverter;
    private $urlGenerator;
    private $downloadLinksUrlGenerator;

    public function __construct(ViewModelConverter $viewModelConverter, UrlGeneratorInterface $urlGenerator, DownloadLinkUriGenerator $downloadLinkUriGenerator)
    {
        $this->viewModelConverter = $viewModelConverter;
        $this->urlGenerator = $urlGenerator;
        $this->downloadLinksUrlGenerator = $downloadLinkUriGenerator;
    }

    /**
     * @param ArticleVersion $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {

        return new ViewModel\ContentAside(
            new ViewModel\ContentAsideStatus('Version of Record',
                'Accepted for publication after peer review and revision.',
            new ViewModel\Link(' About eLife\'s process', $this->urlGenerator->generate('inside-elife-article', ['id' => '54d63486']))),
            new ViewModel\ButtonCollection([
                Button::action('Download', '#downloads', true, 'button-action-download', Button::ACTION_VARIANT_DOWNLOAD),
                Button::action('Cite', '#citations', true, 'button-action-citation', Button::ACTION_VARIANT_CITATION),
                Button::action('Share', '#share', true, 'button-action-share', Button::ACTION_VARIANT_SHARE),
                Button::action('Comment<span aria-hidden=\'true\'><span data-visible-annotation-count></span> </span><span class=\'visuallyhidden\'>Open annotations (there are currently <span data-hypothesis-annotation-count>0</span> annotations on this page). </span>', '#comment', true, null, Button::ACTION_VARIANT_COMMENT),
            ], true),
            !empty($context['metrics']) ? ViewModel\ContextualData::withMetrics($context['metrics']) : null,
            !empty($context['timeline']) ? ViewModel\DefinitionList::timeline($context['timeline']) : null,
            $context['relatedItem'] ?? null
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof ArticleVersion && ViewModel\ContentAside::class === $viewModel;
    }

    protected function getViewModelConverter() : ViewModelConverter
    {
        return $this->viewModelConverter;
    }
}
