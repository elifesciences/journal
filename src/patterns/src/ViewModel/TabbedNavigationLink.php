<?php

namespace eLife\Patterns\ViewModel;

use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\CastsToArray;

final class TabbedNavigationLink implements CastsToArray
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $name;
    private $url;
    private $classes;
    private $isSideBySide;

    private function __construct(string $name, string $url, string $classes = null, bool $isSideBySide = false)
    {
        $this->name = $name;
        $this->url = $url;
        $this->classes = $classes;
        
        if ($isSideBySide) {
            $this->classes = ' tabbed-navigation__tab-label--side-by-side';
            $this->isSideBySide = $isSideBySide;
        }
    }

    public static function fromLink(Link $link, string $classes = null, bool $isSideBySide = false)
    {
        return new static($link['name'], $link['url'], $classes, $isSideBySide);
    }
}
