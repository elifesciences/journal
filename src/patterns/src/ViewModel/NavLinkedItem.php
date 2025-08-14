<?php

namespace eLife\Patterns\ViewModel;

use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class NavLinkedItem implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $button;
    private $path;
    private $rel;
    private $text;

    private function __construct()
    {
    }

    public static function asLink(
        Link $link,
        bool $search = false
    ) : NavLinkedItem {
        $itemAsText = new static();
        $itemAsText->text = $link['name'];
        $itemAsText->path = $link['url'];
        $itemAsText->rel = $search ? 'search' : null;

        return $itemAsText;
    }

    public static function asButton(Button $button) : NavLinkedItem
    {
        $itemAsButton = new static();
        $itemAsButton->button = $button;

        return $itemAsButton;
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/nav-linked-item.mustache';
    }
}
