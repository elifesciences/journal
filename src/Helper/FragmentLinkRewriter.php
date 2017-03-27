<?php

namespace eLife\Journal\Helper;

final class FragmentLinkRewriter
{
    public function rewrite(string $html, string $alternateFragmentPage) : string
    {
        preg_match_all('/id=["\']([^\s]+)["\']/', $html, $matches);

        if (empty($matches)) {
            return $html;
        }

        return preg_replace_callback('/href=["\']#([^\s]+?)["\']/', function (array $match) use ($alternateFragmentPage, $matches) {
            if (in_array($match[1], $matches[1])) {
                return $match[0];
            }

            return sprintf('href="%s#%s"', $alternateFragmentPage, $match[1]);
        }, $html);
    }
}
