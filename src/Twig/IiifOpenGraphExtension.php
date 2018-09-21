<?php

namespace eLife\Journal\Twig;

use eLife\ApiSdk\Model\Image;
use eLife\Journal\Helper\CreatesIiifUri;
use Twig_Extension;
use Twig_Function;
use function min;

final class IiifOpenGraphExtension extends Twig_Extension
{
    use CreatesIiifUri;

    public function getFunctions()
    {
        return [
            new Twig_Function('iiif_og_image', [$this, 'createOpenGraphImage'], ['is_safe' => ['html']]),
        ];
    }

    public function createOpenGraphImage(Image $image) : string
    {
        if ($image->getWidth() > $image->getHeight()) {
            $width = min($image->getWidth(), 2000);
            $height = (int) ($width * ($image->getHeight() / $image->getWidth()));
        } else {
            $height = min($image->getHeight(), 2000);
            $width = (int) ($height * ($image->getWidth() / $image->getHeight()));
        }

        return "<meta property=\"og:image\" content=\"{$this->iiifUri($image, $width, $height)}\">
            <meta property=\"og:image:width\" content=\"{$width}\">
            <meta property=\"og:image:height\" content=\"{$height}\">";
    }
}
