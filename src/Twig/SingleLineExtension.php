<?php

namespace eLife\Journal\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class SingleLineExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('single_line', [$this, 'singleLine']),
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
