<?php

namespace eLife\Journal\Twig;

use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Block\Section;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class TexExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('html2tex', [$this, 'html2tex']),
            new TwigFilter('item2tex', [$this, 'item2tex']),
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

    /**
     * @param Section|Paragraph $contentItem
     */
    public function item2tex($contentItem) : string
    {
        if ($contentItem instanceof Section) {
            $section = [
                $this->html2tex('<b>'.$contentItem->getTitle().'</b>'),
            ];

            foreach ($contentItem->getContent() as $paragraph) {
                $section[] = $this->item2tex($paragraph);
            }

            return implode(' ', $section);
        }

        return $this->html2tex($contentItem->getText());
    }

    public function getName() : string
    {
        return 'tex';
    }
}
