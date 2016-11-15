<?php

namespace eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel;

final class FileAdditionalAssetConverter implements ViewModelConverter
{
    /**
     * @param Block\File $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $download = ViewModel\DownloadLink::fromLink(
            new ViewModel\Link('Download '.$object->getFilename(), $object->getUri()),
            $object->getFilename()
        );

        if (!$object->getDoi()) {
            return ViewModel\AdditionalAsset::withoutDoi(
                $object->getId(),
                $object->getLabel(),
                $download,
                $object->getTitle(),
                implode(' ', array_map(function (Block\Paragraph $block) {
                    return $block->getText();
                }, $object->getCaption()))
            );
        }

        return ViewModel\AdditionalAsset::withDoi(
            $object->getId(),
            $object->getLabel(),
            $download,
            $object->getTitle(),
            new ViewModel\Doi($object->getDoi()),
            implode(' ', array_map(function (Block\Paragraph $block) {
                return $block->getText();
            }, $object->getCaption()))
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Block\File;
    }
}
