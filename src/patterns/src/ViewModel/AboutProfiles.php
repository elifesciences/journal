<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class AboutProfiles implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $heading;
    private $compact;
    private $items;

    public function __construct(array $items, ListHeading $heading = null, bool $compact = false)
    {
        Assertion::notEmpty($items);
        Assertion::allIsInstanceOf($items, AboutProfile::class);

        $this->heading = $heading;
        if ($compact) {
            $this->compact = $compact;
        }
        $this->items = $items;
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/about-profiles.mustache';
    }
}
