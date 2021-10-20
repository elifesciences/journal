<?php

namespace eLife\Journal\Twig;

use eLife\ApiSdk\Model\Image;
use eLife\Journal\Helper\CreatesIiifUri;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class IiifExtension extends AbstractExtension
{
    const MAX_SIZE = 2000;

    use CreatesIiifUri;

    public function getFunctions()
    {
        return [
            new TwigFunction('iiif_uri', [$this, 'createImageUri'], ['is_safe' => ['html']]),
            new TwigFunction('iiif_og_image', [$this, 'createOpenGraphImage'], ['is_safe' => ['html']]),
        ];
    }

    public function createImageUri(Image $image) : string
    {
        list($width, $height) = $this->determineSizes($image->getWidth(), $image->getHeight());

        return $this->iiifUri($image, $width, $height);
    }

    public function createOpenGraphImage(Image $image) : string
    {
        list($width, $height) = $this->determineSizes($image->getWidth(), $image->getHeight(), self::MAX_SIZE);

        return "<meta name=\"twitter:image\" content=\"{$this->iiifUri($image, $width, $height)}\">
            <meta property=\"og:image\" content=\"{$this->iiifUri($image, $width, $height)}\">
            <meta property=\"og:image:width\" content=\"{$width}\">
            <meta property=\"og:image:height\" content=\"{$height}\">";
    }
}
