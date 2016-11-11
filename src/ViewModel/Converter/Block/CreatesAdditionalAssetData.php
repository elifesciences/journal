<?php

namespace eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Patterns\ViewModel;

trait CreatesAdditionalAssetData
{
    final private function createAdditionalAssetData(Block\File $object) : ViewModel\AdditionalAssetData
    {
        $download = ViewModel\DownloadLink::fromLink(
            new ViewModel\Link('Download '.$object->getFilename(), $object->getUri()),
            $object->getFilename()
        );

        if (!$object->getDoi()) {
            return ViewModel\AdditionalAssetData::withoutDoi(
                $object->getId(),
                $object->getLabel(),
                $download,
                $object->getTitle(),
                implode(' ', array_map(function (Block\Paragraph $block) {
                    return $block->getText();
                }, $object->getCaption()))
            );
        }

        return ViewModel\AdditionalAssetData::withDoi(
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
}
