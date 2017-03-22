<?php

namespace eLife\Journal\Twig;

use Twig_Extension;
use Twig_Filter;

final class RisExtension extends Twig_Extension
{
    public function getFilters()
    {
        return [
            new Twig_Filter('html2ris', [$this, 'html2ris']),
        ];
    }

    public function html2ris(string $string) : string
    {
        return htmlspecialchars_decode(strip_tags($string));
    }
}
