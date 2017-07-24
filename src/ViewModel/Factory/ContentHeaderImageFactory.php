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

    public function forLocalFile(string $filename): ViewModel\ContentHeaderImage
    {
        return new ViewModel\ContentHeaderImage(
            $this->pictureForLocalFile($filename),
            'Illustration by <a href="http://www.davidebonazzi.com/">Davide Bonazzi</a>'
        );
    }

    public function forImage(Image $image): ViewModel\ContentHeaderImage
    {
        return new ViewModel\ContentHeaderImage(
            $this->pictureForImage($image)
        );
    }

    public function pictureForLocalFile(string $filename): ViewModel\Picture
    {
        return $this->create(function (int $width, int $height) use ($filename) {
            return $this->packages->getUrl("assets/images/banners/{$filename}-{$width}x{$height}.jpg");
        });
    }

    public function pictureForImage(Image $image): ViewModel\Picture
    {
        return $this->create(function (int $width, int $height) use ($image) {
            return $this->iiifUri($image, $width, $height);
        });
    }

    private function create(callable $callback): ViewModel\Picture
    {
        $sources = [];

        foreach ([450 => 264, 767 => 264, 1023 => 288] as $width => $height) {
            if (empty($srcset = $this->createSrcset($callback, $width, $height))) {
                continue;
            }

            $sources[] = [
                'srcset' => $this->srcsetToString($srcset),
                'media' => "(max-width: {$width}px)",
            ];
        }

        $srcset = $this->createSrcset($callback, 1114, 336);
        $default = end($srcset);
        reset($srcset);

        return new ViewModel\Picture(
            $sources,
            new ViewModel\Image($default, count($srcset) > 1 ? $srcset : [])
        );
    }

    private function createSrcset(callable $callback, int $width, int $height): array
    {
        return array_reduce(range(2, 1), function (array $carry, int $factor) use ($callback, $width, $height) {
            try {
                $carry[$width * $factor] = $callback($width * $factor, $height * $factor);
            } catch (Exception $e) {
                // Do nothing.
            }

            return $carry;
        }, []);
    }

    private function srcsetToString(array $srcset): string
    {
        return implode(', ', array_map(function (int $width, string $uri) {
            return "{$uri} {$width}w";
        }, array_keys($srcset), array_values($srcset)));
    }
}
