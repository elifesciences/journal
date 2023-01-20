<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\HasImpactStatement;
use eLife\ApiSdk\Model\Subject;
use eLife\Journal\Helper\CanConvertContent;
use eLife\Journal\Helper\LicenceUri;
use eLife\Journal\Helper\ModelName;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\Button;
use eLife\Patterns\ViewModel\ContextualData;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use function strip_tags;

final class ContentAsideConverter implements ViewModelConverter
{
    use CanConvertContent;
    use CreatesDate;

    private $viewModelConverter;
    private $urlGenerator;

    public function __construct(ViewModelConverter $viewModelConverter, UrlGeneratorInterface $urlGenerator)
    {
        $this->viewModelConverter = $viewModelConverter;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param ArticleVersion $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        if (!empty($context['metrics'])) {
            $contextualData = ContextualData::withMetrics($context['metrics']);
        }
        return new ViewModel\ContentAside(
            new ViewModel\ContentAsideStatus('Research article',
                'The author(s) have declared this to be the current/final version.',
            new ViewModel\Link(' About eLife\'s process', '#')),
            new ViewModel\ButtonCollection([
                Button::action('Download', '#', true, 'button-action-download', Button::ACTION_VARIANT_DOWNLOAD),
                Button::action('Cite', '#', true, 'button-action-citation', Button::ACTION_VARIANT_CITATION),
                Button::action('Share', '#', true, 'button-action-share', Button::ACTION_VARIANT_SHARE),
                Button::action('Comment', '#', true, null, Button::ACTION_VARIANT_COMMENT),
            ], true),
            $contextualData ?? null,
            ViewModel\DefinitionList::timeline(['test' => 'test test'])
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
