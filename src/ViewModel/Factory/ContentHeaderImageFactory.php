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

    private $packages;

    public function __construct(Packages $packages)
    {
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
        return $this->create(function (int $width, int $height, string $extension) use ($filename) {
            return $this->packages->getUrl("assets/images/banners/{$filename}-{$width}x{$height}.{$extension}");
        });
    }

    public function pictureForImage(Image $image) : ViewModel\Picture
    {
        return $this->create(function (int $width, int $height, string $extension) use ($image) {
            return $this->iiifUri($image, $width, $height, $extension);
        }, $image->getSource()->getMediaType(), $image->getWidth(), $image->getHeight());
    }

    private function create(callable $callback, string $source = 'image/jpeg', int $width = null, int $height = null) : ViewModel\Picture
    {
        if ('image/png' === $source) {
            $fallbackFormat = 'image/png';
        } else {
            $fallbackFormat = 'image/jpeg';
        }

        $builder = new PictureBuilder(function (string $format = null, int $width = null, int $height = null) use ($callback, $fallbackFormat) {
            $width = $width ?? 1114;
            $height = $height ?? 336;
            $extension = MediaTypes::toExtension($format ?? $fallbackFormat);

            return $callback($width, $height, $extension);
        });

        if ($width && $height) {
            $builder = $builder->setOriginalSize($width, $height);
        }

        $builder = $builder
            ->addType($fallbackFormat)
            ->addSize(450, 264, '(max-width: 450px)')
            ->addSize(767, 264, '(max-width: 767px)')
            ->addSize(1023, 288, '(max-width: 1023px)')
            ->addSize(1114, 336);

        return $builder->build();
    }
}
