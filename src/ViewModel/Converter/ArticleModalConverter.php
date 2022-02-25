<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\Reference\JournalReference;
use eLife\Journal\Helper\CanConvert;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;

final class ArticleModalConverter implements ViewModelConverter
{
    use CanConvert;

    private $viewModelConverter;
    private $patternRenderer;

    public function __construct(ViewModelConverter $viewModelConverter, PatternRenderer $patternRenderer)
    {
        $this->viewModelConverter = $viewModelConverter;
        $this->patternRenderer = $patternRenderer;
    }

    /**
     * @param ArticleVersion $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        if ('social' === ($context['type'] ?? 'social')) {
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
                    "https://doi.org/{$object->getDoi()}"
                ),
                ViewModel\Button::clipboard('Copy to clipboard', "https://doi.org/{$object->getDoi()}"),
                new ViewModel\SocialMediaSharers(
                    strip_tags($object->getFullTitle()),
                    "https://doi.org/{$object->getDoi()}"
                ),
            ];
            return ViewModel\ModalWindow::small('Share this article', $this->patternRenderer->render(...$body), null, 'modalContentShare');
        } else {
            $body = [
                ViewModel\Reference::withDoi(
                    $object->getFullTitle(),
                    new ViewModel\Doi($object->getDoi())
                ),
            ];
            return ViewModel\ModalWindow::create('Cite this article', $this->patternRenderer->render(...$body), null, 'modalContentCitations');
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
