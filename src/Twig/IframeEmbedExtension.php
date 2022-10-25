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
        $content = preg_replace('/<p class="paragraph">We are now excited to introduce.+<\/p>/', '<p class="paragraph"><a href="https://mosaically.com/embed/7ebca38f-d7ae-45bc-a8bf-0080f486314a">Embed IFrame</a><br>attribute-scrolling: no<br> attribute-width: 100%<br> attribute-height: 480<br> attribute-frameBorder: 0<br> attribute-allowfullscreen: null<br>Caption: Photo mosaic by: <a href="https://mosaically.com/elife">elife</a> @ <a href="https://mosaically.com">Mosaically</a></p>', $content);

        $content = preg_replace('/<p class="paragraph">At eLife we’re choosing to do something.+<\/p>/', '<p class="paragraph"><a href="https://www.tiki-toki.com/timeline/embed/1854566/1718310907/">Embed IFrame</a><br>attribute-onmousewheel: blank<br> attribute-frameborder: 0<br> attribute-scrolling: no<br> attribute-style: border-width: 0;<br> attribute-id: tl-timeline-iframe<br> attribute-width: 100%<br> attribute-height: 480</p>', $content);

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
