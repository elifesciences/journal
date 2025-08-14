<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class ProfileSnippet implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $image;
    private $title;
    private $name;

    public function __construct(string $name, string $title, Picture $image = null)
    {
        Assertion::notBlank($name);
        Assertion::notBlank($title);

        $this->name = $name;
        $this->title = $title;
        $this->image = $image;
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/profile-snippet.mustache';
    }
}
