<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\ArticleVoR;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\Journal\Helper\CanConvertContent;
use eLife\Journal\Helper\DoiVersion;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\Button;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ArticleModalConverter implements ViewModelConverter
{
    use CanConvertContent;

    private $viewModelConverter;
    private $patternRenderer;
    private $urlGenerator;

    public function __construct(ViewModelConverter $viewModelConverter, PatternRenderer $patternRenderer, UrlGeneratorInterface $urlGenerator)
    {
        $this->viewModelConverter = $viewModelConverter;
        $this->patternRenderer = $patternRenderer;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param ArticleVersion $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        if ('social' === ($context['type'] ?? 'social')) {
            $doi = (string) new DoiVersion($object);

            $body = [
                ViewModel\TextField::textInput(
                    new ViewModel\FormLabel('Doi', true),
                    'modal-share-doi',
                    'doi',
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    "https://doi.org/{$doi}"
                ),
                ViewModel\Button::clipboard('Copy to clipboard', "https://doi.org/{$doi}"),
                new ViewModel\SocialMediaSharersNew(
                    strip_tags($object->getFullTitle()),
                    "https://doi.org/{$doi}",
                    true,
                    true,
                    false,
                    true
                ),
            ];

            // @todo - switch to use ModalWindow::small when you can work out why it's broken!
            return ViewModel\ModalWindow::create('Share this article', $this->patternRenderer->render(...$body), null, 'modalContentShare');
        } else {
            $reference = $this->patternRenderer->render($this->convertTo($object, ViewModel\Reference::class));

            $body = [
                new ViewModel\ButtonCollection(
                    array_merge(($context['clipboard'] ?? false) ? [Button::clipboard('Copy to clipboard', $context['clipboard'])] : [],
                    [
                        Button::link('Download BibTeX', $this->urlGenerator->generate('article-bibtex', [$object]), Button::SIZE_MEDIUM, Button::STYLE_SECONDARY),
                        Button::link('Download .RIS', $this->urlGenerator->generate('article-ris', [$object]), Button::SIZE_MEDIUM, Button::STYLE_SECONDARY),
                    ])
                ),
            ];

            return ViewModel\ModalWindow::create('Cite this article', $reference.$this->patternRenderer->render(...$body), null, 'modalContentCitations');
        }
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof ArticleVersion && ViewModel\ModalWindow::class === $viewModel && in_array($context['type'] ?? 'social', ['social', 'citation']);
    }

    protected function getViewModelConverter() : ViewModelConverter
    {
        return $this->viewModelConverter;
    }
}
