<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class MainMenu implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $title;
    private $links;
    private $listHeading;

    public function __construct(SiteHeaderTitle $title, array $links)
    {
        Assertion::notEmpty($links);
        Assertion::allIsInstanceOf($links, Link::class);

        $this->title = $title;
        $this->links = ['items' => $links];
        $this->listHeading = new ListHeading('Menu');
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/main-menu.mustache';
    }
}
