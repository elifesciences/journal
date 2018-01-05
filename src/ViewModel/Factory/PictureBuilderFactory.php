<?php

namespace eLife\Journal\ViewModel\Factory;

use eLife\ApiSdk\Model\Image;
use eLife\Journal\Helper\CreatesIiifUri;
use eLife\Journal\Helper\MediaTypes;
use eLife\Journal\ViewModel\Builder\PictureBuilder;

final class PictureBuilderFactory
{
    use CreatesIiifUri;

    public function create(callable $uriGenerator, string $originalType, int $defaultWidth = null, int $defaultHeight = null, string $altText = '') : PictureBuilder
    {
        $defaultType = $originalType;

        if ('image/svg+xml' === $defaultType) {
            $defaultType = 'image/png';
        }

        $builder = new PictureBuilder(function (string $type = null, int $width = null, int $height = null, float $scale = 1) use ($uriGenerator, $defaultType, $defaultWidth, $defaultHeight) {
            return $uriGenerator($type ?? $defaultType, $width ?? $defaultWidth, $height ?? $defaultHeight, $scale);
        }, $altText);

        $builder = $builder
            ->addType($originalType)
            ->addType($defaultType)
            ->addType('image/webp')
        ;

        if ($defaultWidth) {
            $builder = $builder->addSize($defaultWidth, $defaultHeight);
        }

        return $builder;
    }

    public function forImage(Image $image, int $defaultWidth, int $defaultHeight = null) : PictureBuilder
    {
        if ('image/png' === $image->getSource()->getMediaType()) {
            $type = 'image/png';
        } else {
            $type = 'image/jpeg';
        }

        if ($defaultWidth > $image->getWidth() && null === $defaultHeight) {
            $defaultWidth = null;
        }

        $builder = $this->create(function (string $type, int $width = null, int $height = null, float $scale = 1) use ($image) {
            $extension = MediaTypes::toExtension($type);

            if ($width) {
                $width = $width * $scale;
            }
            if ($height) {
                $height = $height * $scale;
            }

            return $this->iiifUri($image, $width, $height, $extension);
        }, $type, $defaultWidth, $defaultHeight, $image->getAltText());

        return $builder->setOriginalSize($image->getWidth(), $image->getHeight());
    }
}
