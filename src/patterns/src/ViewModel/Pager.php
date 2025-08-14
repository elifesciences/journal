<?php

namespace eLife\Patterns\ViewModel;

use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class Pager implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $previousPage;
    private $nextPage;
    private $targetId;

    private function __construct(Link $previousPage = null, Link $nextPage = null, string $targetId = null)
    {
        if ($previousPage) {
            $this->previousPage = Button::link($previousPage['name'], $previousPage['url']);
        }
        if ($nextPage) {
            $this->nextPage = Button::link($nextPage['name'], $nextPage['url'], Button::SIZE_MEDIUM, Button::STYLE_DEFAULT, true, null === $previousPage);
        }
        $this->targetId = $targetId;
    }

    public static function firstPage(Link $nextPage, string $targetId = null) : Pager
    {
        return new self(null, $nextPage, $targetId);
    }

    public static function subsequentPage(Link $previousPage, Link $nextPage = null, string $targetId = null) : Pager
    {
        return new self($previousPage, $nextPage, $targetId);
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/pager.mustache';
    }
}
