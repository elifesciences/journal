<?php

namespace eLife\Journal\Twig;

use Twig_Extension;
use Twig_Filter;

final class TexExtension extends Twig_Extension
{
    public function getFilters()
    {
        return [
            new Twig_Filter('html2tex', [$this, 'html2tex']),
        ];
    }

    public function html2tex(string $string) : string
    {
        static $replacements = [
            '\\' => '\textbackslash',
            '%' => '\%',
            '$' => '\$',
            '#' => '\#',
            '_' => '\_',
            '{' => '\textbraceleft',
            '}' => '\textbraceright',
            '~' => '\textasciitilde',
            '^' => '\textasciicircum',
            '<b>' => '\textbf{',
            '<i>' => '\textit{',
            '<span class="underline">' => '\uline{',
            '<span class="monospace">' => '\texttt{',
            '<span class="small-caps">' => '\textsc{',
            '<sub>' => '\textsubscript{',
            '<sup>' => '\textsuperscript{',
            '</b>' => '}',
            '</i>' => '}',
            '</span>' => '}',
            '</sub>' => '}',
            '</sup>' => '}',
        ];

        return str_replace('&', '\&', htmlspecialchars_decode(strip_tags(str_replace(array_keys($replacements), array_values($replacements), $string))));
    }

    public function getName() : string
    {
        return 'tex';
    }
}
