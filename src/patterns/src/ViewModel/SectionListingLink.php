<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class SectionListingLink implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $text;
    private $targetFragmentId;

    public function __construct(string $text, string $targetFragmentId)
    {
        Assertion::notBlank($text);
        Assertion::notBlank($targetFragmentId);

        $this->text = $text;
        $this->targetFragmentId = $targetFragmentId;
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/section-listing-link.mustache';
    }
}
