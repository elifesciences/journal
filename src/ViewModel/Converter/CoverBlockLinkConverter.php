<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Cover;
use eLife\Journal\Helper\CreatesIiifUri;
use eLife\Journal\Helper\MediaTypes;
use eLife\Journal\ViewModel\Builder\PictureBuilder;
use eLife\Patterns\ViewModel;

final class CoverBlockLinkConverter implements ViewModelConverter
{
    use CreatesIiifUri;

    /**
     * @param Cover $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $image = $object->getBanner();

        $builder = new PictureBuilder(function (string $format = null, int $width = null, int $height = null) use ($image) {
            $width = $width ?? 263;
            $height = $height ?? 176;

            if ('image/png' === $image->getSource()->getMediaType()) {
                $fallbackFormat = 'image/png';
            } else {
                $fallbackFormat = 'image/jpeg';
            }

            $extension = MediaTypes::toExtension($format ?? $fallbackFormat);

            return $this->iiifUri($image, $width, $height, $extension);
        });

        $builder = $builder->setOriginalSize($image->getWidth(), $image->getHeight());

        $builder = $builder
            ->addType('image/jpeg')
            ->addSize(263, 176);

        return new ViewModel\BlockLink(
            $context['link'],
            $builder->build()
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Cover && ViewModel\BlockLink::class === $viewModel && isset($context['link']);
    }
}
