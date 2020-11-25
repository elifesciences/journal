<?php

namespace eLife\Journal\Twig;

use eLife\ApiSdk\Model\Image;
use eLife\Journal\Helper\CreatesIiifUri;
use Twig_Extension;
use Twig_Function;
use function min;

final class IiifExtension extends Twig_Extension
{
    const MAX_SIZE = 2000;

    use CreatesIiifUri;

    public function getFunctions()
    {
        return [
            new Twig_Function('iiif_uri', [$this, 'createImageUri'], ['is_safe' => ['html']]),
            new Twig_Function('iiif_og_image', [$this, 'createOpenGraphImage'], ['is_safe' => ['html']]),
        ];
    }

    public function createImageUri(Image $image) : string
    {
        list($width, $height) = $this->determineSizes($image->getWidth(), $image->getHeight());

        return $this->iiifUri($image, $width, $height);
    }

    public function createOpenGraphImage(Image $image) : string
    {
        list($width, $height) = $this->determineSizes($image->getWidth(), $image->getHeight());

        return "<meta name=\"twitter:image\" content=\"{$this->iiifUri($image, $width, $height)}\">
            <meta property=\"og:image\" content=\"{$this->iiifUri($image, $width, $height)}\">
            <meta property=\"og:image:width\" content=\"{$width}\">
            <meta property=\"og:image:height\" content=\"{$height}\">";
    }

    private function determineSizes(int $width, int $height) : array
    {
        if ($width > $height) {
            $min = min($width, self::MAX_SIZE);
            return [
                $min,
                (int) ($min * ($height / $width)),
            ];
        } else {
            $min = min($height, self::MAX_SIZE);
            return [
                (int) ($min * ($width / $height)),
                $min,
            ];
        }
    }
}
