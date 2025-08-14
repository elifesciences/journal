<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class SiteHeaderTitle implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $homePagePath;
    private $isWrapped;
    private $borderVariant;

    public function __construct(
        string $homePagePath,
        bool $isWrapped = false,
        bool $borderVariant = false
    )
    {
        Assertion::notBlank($homePagePath);

        $this->homePagePath = $homePagePath;
        $this->isWrapped = $isWrapped;
        $this->borderVariant = $borderVariant;
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/site-header-title.mustache';
    }
}
