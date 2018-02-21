<?php

namespace eLife\Journal\ViewModel\Factory;

use eLife\ApiSdk\Model\Image;
use eLife\Journal\Helper\CreatesIiifUri;
use eLife\Journal\Helper\MediaTypes;
use eLife\Journal\ViewModel\Builder\PictureBuilder;
use eLife\Patterns\ViewModel;
use Symfony\Component\Asset\Packages;

final class ContentHeaderImageFactory
{
    use CreatesIiifUri;

    private $pictureBuilderFactory;
    private $packages;

    public function __construct(PictureBuilderFactory $pictureBuilderFactory, Packages $packages)
    {
        $this->pictureBuilderFactory = $pictureBuilderFactory;
        $this->packages = $packages;
    }

    public function forLocalFile(string $filename, bool $creditOverlay = false) : ViewModel\ContentHeaderImage
    {
        return new ViewModel\ContentHeaderImage(
            $this->pictureForLocalFile($filename),
            'Illustration by <a href="http://www.davidebonazzi.com/">Davide Bonazzi</a>',
            $creditOverlay
        );
    }

    public function forImage(Image $image, bool $creditOverlay = false) : ViewModel\ContentHeaderImage
    {
        return new ViewModel\ContentHeaderImage(
            $this->pictureForImage($image),
            $image->getAttribution()->notEmpty() ? implode(' ', $image->getAttribution()->toArray()) : null,
            $creditOverlay
        );
    }

    public function pictureForLocalFile(string $filename) : ViewModel\Picture
    {
        $builder = $this->pictureBuilderFactory
            ->create(function (string $type, int $width, int $height = null) use ($filename) {
                $extension = MediaTypes::toExtension($type);

                return $this->packages->getUrl("assets/images/banners/{$filename}-{$width}x{$height}.{$extension}");
            }, 'image/jpeg', 1114, 336);

        return $this->addSizes($builder)->build();
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
