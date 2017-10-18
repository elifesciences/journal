<?php

namespace eLife\Journal\ViewModel\Factory;

use eLife\ApiSdk\Model\Image;
use eLife\Journal\Helper\CreatesIiifUri;
use eLife\Patterns\ViewModel;
use Exception;
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
        return $this->create(function (int $width, int $height, string $extension = null) use ($filename) {
            $extension = $extension ?? 'jpg';

            return $this->packages->getUrl("assets/images/banners/{$filename}-{$width}x{$height}.{$extension}");
        });
    }

    public function pictureForImage(Image $image) : ViewModel\Picture
    {
        return $this->create(function (int $width, int $height, string $extension = null) use ($image) {
            return $this->iiifUri($image, $width, $height, $extension);
        });
    }

    private function create(callable $callback) : ViewModel\Picture
    {
        $sources = [];

        foreach (['image/webp' => 'webp', '' => null] as $contentType => $extension) {
            foreach ([450 => 264, 767 => 264, 1023 => 288, 1114 => 336] as $width => $height) {
                if (empty($srcset = $this->createSrcset($callback, $width, $height, $extension))) {
                    continue;
                }

                $source = array_filter([
                    'srcset' => $this->srcsetToString($srcset),
                    'media' => "(max-width: {$width}px)",
                    'type' => $contentType,
                ]);

                if (1114 === $width) {
                    unset($source['media']);
                }

                $sources[] = $source;
            }
        }

        return new ViewModel\Picture(
            $sources,
            new ViewModel\Image($callback(1114, 336))
        );
    }

    private function createSrcset(callable $callback, int $width, int $height, string $extension = null) : array
    {
        return array_reduce(range(2, 1), function (array $carry, int $factor) use ($callback, $width, $height, $extension) {
            try {
                $carry[$width * $factor] = $callback($width * $factor, $height * $factor, $extension);
            } catch (Exception $e) {
                // Do nothing.
            }

            return $carry;
        }, []);
    }

    private function srcsetToString(array $srcset) : string
    {
        return implode(', ', array_map(function (int $width, string $uri) {
            return "{$uri} {$width}w";
        }, array_keys($srcset), array_values($srcset)));
    }
}
