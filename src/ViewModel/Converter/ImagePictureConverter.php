<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Image;
use eLife\Journal\Helper\CreatesIiifUri;
use eLife\Journal\ViewModel\Factory\PictureBuilderFactory;
use eLife\Patterns\ViewModel;

final class ImagePictureConverter implements ViewModelConverter
{
    use CreatesIiifUri;

    private $pictureBuilderFactory;

    public function __construct(PictureBuilderFactory $pictureBuilderFactory)
    {
        $this->pictureBuilderFactory = $pictureBuilderFactory;
    }

    /**
     * @param Image $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $builder = $this->pictureBuilderFactory->forImage($object, $context['width'], $context['height'] ?? null);

        return $builder->build();
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Image && !empty($context['width']);
    }
}
