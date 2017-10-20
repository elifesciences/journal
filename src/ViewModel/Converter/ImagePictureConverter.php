<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Image;
use eLife\Journal\Helper\CreatesIiifUri;
use eLife\Journal\Helper\MediaTypes;
use eLife\Journal\ViewModel\Builder\PictureBuilder;
use eLife\Patterns\ViewModel;

final class ImagePictureConverter implements ViewModelConverter
{
    use CreatesIiifUri;

    /**
     * @param Image $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $builder = new PictureBuilder(function (string $format = null, int $width = null, int $height = null) use ($object, $context) {
            return $this->iiifUri($object, $width ?? $context['width'], $height ?? ($context['height'] ?? null), MediaTypes::toExtension($format ?? 'image/jpeg'));
        }, $object->getAltText());

        if ('image/png' === $object->getSource()->getMediaType()) {
            $builder = $builder->addType('image/png');
        } else {
            $builder = $builder->addType('image/jpeg');
        }

        $builder = $builder->addSize($context['width'], $context['height'] ?? null);

        return $builder->build();
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Image && !empty($context['width']);
    }
}
