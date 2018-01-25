<?php

namespace eLife\Journal\Helper;

final class Html
{
    private function __construct()
    {
    }

    public static function stripElement(string $html, string $element) : string
    {
        // Assumes well-formed HTML.
        return preg_replace("~</?{$element}(\s.*?)?>~i", '', $html);
    }
}
