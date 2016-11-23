<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\File;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;

final class FileAdditionalAssetConverter implements ViewModelConverter
{
    use HasPatternRenderer;

    private $viewModelConverter;
    private $patternRenderer;

    public function __construct(ViewModelConverter $viewModelConverter, PatternRenderer $patternRenderer)
    {
        $this->viewModelConverter = $viewModelConverter;
        $this->patternRenderer = $patternRenderer;
    }

    /**
     * @param File $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $caption = array_map(function (Block $block) {
            return $this->viewModelConverter->convert($block);
        }, $object->getCaption());

        $captionText = new ViewModel\CaptionText(
            $object->getLabel(),
            $object->getTitle(),
            $this->patternRenderer->render(...$caption)
        );

        $download = ViewModel\DownloadLink::fromLink(
            new ViewModel\Link('Download '.$object->getFilename(), $object->getUri()),
            $object->getFilename()
        );

        if (!$object->getDoi()) {
            return ViewModel\AdditionalAsset::withoutDoi($object->getId(), $captionText, $download, $object->getUri());
        }

        return ViewModel\AdditionalAsset::withDoi($object->getId(), $captionText, $download, new ViewModel\Doi($object->getDoi()));
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof File;
    }

    protected function getPatternRenderer() : PatternRenderer
    {
        return $this->patternRenderer;
    }
}
