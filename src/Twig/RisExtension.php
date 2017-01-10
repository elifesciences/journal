<?php

namespace eLife\Journal\Twig;

use Twig_Extension;
use Twig_SimpleFilter;

final class RisExtension extends Twig_Extension
{
    public function getFilters()
    {
        return [
            new Twig_SimpleFilter('html2ris', [$this, 'html2ris']),
        ];
    }

    public function html2ris(string $string) : string
    {
        return htmlspecialchars_decode(strip_tags($string));
    }

    public function getName() : string
    {
        return 'ris';
    }
}
