<?php

namespace eLife\Journal\ViewModel\Factory;

use eLife\ApiSdk\Model\Image;
use eLife\Journal\Helper\CreatesIiifUri;
use eLife\Journal\ViewModel\Builder\PictureBuilder;
use eLife\Patterns\ViewModel;
use Symfony\Component\Asset\Packages;

final class CarouselItemImageFactory
{
    use CreatesIiifUri;

    private $pictureBuilderFactory;
    private $packages;

    public function __construct(PictureBuilderFactory $pictureBuilderFactory, Packages $packages)
    {
        $this->pictureBuilderFactory = $pictureBuilderFactory;
        $this->packages = $packages;
    }

    public function forImage(Image $image) : ViewModel\CarouselItemImage
    {
        return new ViewModel\CarouselItemImage(
            $this->pictureForImage($image),
            $image->getAttribution()->notEmpty() ? implode(' ', $image->getAttribution()->toArray()) : null
        );
    }

    public function pictureForImage(Image $image) : ViewModel\Picture
    {
        $builder = $this->pictureBuilderFactory->forImage($image, 1114, 336);

        return $this->addSizes($builder)->build();
    }

    private function addSizes(PictureBuilder $builder) : PictureBuilder
    {
        return $builder
            ->addSize(450, 264, '(max-width: 450px)')
            ->addSize(729, 264, '(max-width: 729px)')
            ->addSize(899, 288, '(max-width: 899px)')
            ->addSize(1023, 336, '(max-width: 1023px)');
    }
}
