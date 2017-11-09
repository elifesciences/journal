<?php

namespace eLife\Journal\ViewModel\Factory;

use eLife\ApiSdk\Model\Image;
use eLife\Journal\Helper\CreatesIiifUri;
use eLife\Journal\Helper\MediaTypes;
use eLife\Journal\ViewModel\Builder\PictureBuilder;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class PictureBuilderFactory
{
    use CreatesIiifUri;

    private $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker = null)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public function create(callable $uriGenerator, string $defaultType, int $defaultWidth = null, int $defaultHeight = null, string $altText = '') : PictureBuilder
    {
        $builder = new PictureBuilder(function (string $type = null, int $width = null, int $height = null) use ($uriGenerator, $defaultType, $defaultWidth, $defaultHeight) {
            return $uriGenerator($type ?? $defaultType, $width ?? $defaultWidth, $height ?? $defaultHeight);
        }, $altText);

        $builder = $builder->addType($defaultType);

        if ('image/svg+xml' === $defaultType) {
            $builder = $builder->addType('image/png');
        }

        if ($this->authorizationChecker && $this->authorizationChecker->isGranted('FEATURE_CAN_VIEW_WEBP')) {
            $builder = $builder->addType('image/webp');
        }

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

        if ($defaultWidth > $image->getWidth()) {
            $defaultWidth = null;
        }
        if ($defaultHeight > $image->getHeight()) {
            $defaultHeight = null;
        }

        $builder = $this->create(function (string $type, int $width = null, int $height = null) use ($image) {
            $extension = MediaTypes::toExtension($type);

            return $this->iiifUri($image, $width, $height, $extension);
        }, $type, $defaultWidth, $defaultHeight, $image->getAltText());

        return $builder->setOriginalSize($image->getWidth(), $image->getHeight());
    }
}
