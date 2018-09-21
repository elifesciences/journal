<?php

namespace eLife\Journal\Twig;

use eLife\ApiSdk\Model\Image;
use eLife\Journal\Helper\CreatesIiifUri;
use Twig_Extension;
use Twig_Function;
use function min;

final class IiifOpenGraphExtension extends Twig_Extension
{
    const MAX_SIZE = 2000;

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
            list($width, $height) = $this->determineSizes($image->getWidth(), $image->getHeight());
        } else {
            list($height, $width) = $this->determineSizes($image->getHeight(), $image->getWidth());
        }

        return "<meta property=\"og:image\" content=\"{$this->iiifUri($image, $width, $height)}\">
            <meta property=\"og:image:width\" content=\"{$width}\">
            <meta property=\"og:image:height\" content=\"{$height}\">";
    }

    private function determineSizes(int $one, int $two) : array
    {
        $returnOne = min($one, self::MAX_SIZE);
        $returnTwo = (int) ($returnOne * ($two / $one));

        return [$returnOne, $returnTwo];
    }
}
