<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\AssetFile;
use eLife\ApiSdk\Model\Block;
use eLife\Journal\Helper\DownloadLink;
use eLife\Journal\Helper\DownloadLinkUriGenerator;
use eLife\Journal\Helper\HasPatternRenderer;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;

final class AssetFileAdditionalAssetConverter implements ViewModelConverter
{
    use HasPatternRenderer;

    private $viewModelConverter;
    private $patternRenderer;
    private $downloadLinkUriGenerator;

    public function __construct(ViewModelConverter $viewModelConverter, PatternRenderer $patternRenderer, DownloadLinkUriGenerator $downloadLinkUriGenerator)
    {
        $this->viewModelConverter = $viewModelConverter;
        $this->patternRenderer = $patternRenderer;
        $this->downloadLinkUriGenerator = $downloadLinkUriGenerator;
    }

    /**
     * @param AssetFile $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $caption = $object->getCaption()->map(function (Block $block) {
            return $this->viewModelConverter->convert($block);
        });

        if ($caption->notEmpty()) {
            $text = $this->patternRenderer->render(...$caption);
        } else {
            $text = null;
        }

        $captionText = ViewModel\CaptionText::withHeading($object->getLabel(), $object->getTitle(), $text);

        $download = new ViewModel\DownloadLink(
            new ViewModel\Link('Download '.$object->getFile()->getFilename(), $this->downloadLinkUriGenerator->generate(new DownloadLink($object->getFile()->getUri(), $object->getFile()->getFilename()))),
            $object->getFile()->getFilename()
        );

        if (!$object->getDoi()) {
            return ViewModel\AdditionalAsset::withoutDoi($object->getId(), $captionText, $download, $object->getFile()->getUri());
        }

        return ViewModel\AdditionalAsset::withDoi($object->getId(), $captionText, $download, new ViewModel\Doi($object->getDoi()));
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof AssetFile;
    }

    protected function getPatternRenderer() : PatternRenderer
    {
        return $this->patternRenderer;
    }
}
