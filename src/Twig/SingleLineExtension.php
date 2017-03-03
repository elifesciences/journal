<?php

namespace eLife\Journal\Twig;

use Twig_Extension;
use Twig_SimpleFilter;

final class SingleLineExtension extends Twig_Extension
{
    public function getFilters()
    {
        return [
            new Twig_SimpleFilter('single_line', [$this, 'singleLine']),
        ];
    }

    public function singleLine(string $string) : string
    {
        return preg_replace('/\s+/S', ' ', $string);
    }

    public function getName() : string
    {
        return 'single_line';
    }
}
