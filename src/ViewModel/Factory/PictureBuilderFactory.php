<?php

namespace eLife\Journal\ViewModel\Factory;

use eLife\ApiSdk\Model\Image;
use eLife\Journal\Helper\CreatesIiifUri;
use eLife\Journal\Helper\MediaTypes;
use eLife\Journal\ViewModel\Builder\PictureBuilder;

final class PictureBuilderFactory
{
    use CreatesIiifUri;

    public function create(callable $uriGenerator, string $type, int $defaultWidth = null, int $defaultHeight = null, string $altText = '') : PictureBuilder
    {
        $builder = new PictureBuilder(function (string $format = null, int $width = null, int $height = null) use ($uriGenerator, $type, $defaultWidth, $defaultHeight) {
            return $uriGenerator($format ?? $type, $width ?? $defaultWidth, $height ?? $defaultHeight);
        }, $altText);

        $builder = $builder->addType($type);

        if ('image/svg+xml' === $type) {
            $builder = $builder->addType('image/png');
        }

        if ($defaultWidth) {
            $builder = $builder->addSize($defaultWidth, $defaultHeight);
        }

        return $builder;
    }

    public function forImage(Image $image, int $defaultWidth, int $defaultHeight = null) : PictureBuilder
    {
        if ('image/png' === $image->getSource()->getMediaType()) {
            $format = 'image/png';
        } else {
            $format = 'image/jpeg';
        }

        $builder = $this->create(function (string $format, int $width, int $height = null) use ($image) {
            $extension = MediaTypes::toExtension($format);

            return $this->iiifUri($image, $width, $height, $extension);
        }, $format, $defaultWidth, $defaultHeight, $image->getAltText());

        return $builder
            ->setOriginalSize($image->getWidth(), $image->getHeight());
    }
}
