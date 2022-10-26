<?php

namespace eLife\Journal\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class IframeEmbedExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter(
                'iframe_embed',
                [$this, 'iframeEmbed'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    public function iframeEmbed(string $content) : string
    {
        if (preg_match('/<a[^>]* href="(?P<iframe>[^\"]+)"[^>]*>Embed IFrame<\/a>/', $content, $matches)) {
            $attrs = [];
            if (preg_match_all('/attribute\-(?P<attr>[^:]+):[\s]+(?P<value>[^<]+)[\s]*/', $content, $attrMatches)) {
                $attrs = array_combine($attrMatches['attr'], $attrMatches['value']);
            }

            $caption = null;

            if (preg_match('/Caption:[\s]+(?P<caption>.+)<\/p>/', $content, $capMatches)) {
                $caption = $capMatches['caption'];
            }

            $attributes = implode('', array_map(function ($a, $v) {
                $out = ' '.$a;

                if ('null' !== $v) {
                    $out .= '="'.('blank' !== $v ? $v : '').'"';
                }

                return $out;
            }, array_keys($attrs), $attrs));

            return preg_replace('/<p class="paragraph"><a [^>]+>Embed IFrame.+<\/p>/', '<figure class="iframe-embed"><iframe src="'.$matches['iframe'].'"'.$attributes.'></iframe>'.($caption ? '<small>'.$caption.'</small>' : '').'</figure>', $content);
        }

        return $content;
    }

    public function getName() : string
    {
        return 'iframe_embed';
    }
}
