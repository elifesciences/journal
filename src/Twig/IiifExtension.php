<?php

namespace eLife\Journal\Twig;

use eLife\ApiSdk\Model\Image;
use eLife\Journal\Helper\CreatesIiifUri;
use Twig_Extension;
use Twig_Function;

final class IiifExtension extends Twig_Extension
{
    use CreatesIiifUri;

    public function getFunctions()
    {
        return [
            new Twig_Function('iiif_uri', [$this, 'generateIiifUri']),
        ];
    }

    public function generateIiifUri(Image $string) : string
    {
        return $this->iiifUri($string);
    }
}
