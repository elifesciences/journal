<?php

namespace eLife\Journal\Twig;

use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Block\Section;
use Twig_Extension;
use Twig_Filter;

final class RisExtension extends Twig_Extension
{
    public function getFilters()
    {
        return [
            new Twig_Filter('html2ris', [$this, 'html2ris']),
            new Twig_Filter('item2ris', [$this, 'item2ris']),
        ];
    }

    public function html2ris(string $string) : string
    {
        return htmlspecialchars_decode(strip_tags($string));
    }

    /**
     * @param Section|Paragraph $contentItem
     */
    public function item2ris($contentItem) : string
    {
        if ($contentItem instanceof Section) {
            $section = [
                $this->html2ris(rtrim($contentItem->getTitle(), '.').'.'),
            ];

            foreach ($contentItem->getContent() as $paragraph) {
                $section[] = $this->item2ris($paragraph);
            }

            return implode(' ', $section);
        }

        return $this->html2ris($contentItem->getText());
    }
}
