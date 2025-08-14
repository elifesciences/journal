<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class ViewSelector implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $primaryUrl;
    private $primaryLabel;
    private $jumpLinks;
    private $secondaryUrl = null;
    private $secondaryLabel = null;
    private $secondaryIsActive;
    private $isTabSelector;
    private $otherLinks;
    private $sideBySideUrl;

    public function __construct(
        Link $primaryLink,
        array $jumpLinks = [],
        Link $secondaryLink = null,
        bool $secondaryIsActive = false,
        bool $isTabSelector = false,
        string $sideBySideUrl = null,
        array $otherLinks = []
    ) {
        Assertion::allIsInstanceOf($jumpLinks, Link::class);
        if (count($jumpLinks) > 0) {
            Assertion::min(count($jumpLinks), 2);
        }
        Assertion::allIsInstanceOf($otherLinks, Link::class);

        $primaryLinkArray = $primaryLink->toArray();
        Assertion::notBlank($primaryLinkArray['url']);
        $this->primaryUrl = $primaryLinkArray['url'];
        $this->primaryLabel = $primaryLinkArray['name'];

        if (count($jumpLinks) > 0) {
            $this->jumpLinks = ['links' => $jumpLinks];
        }

        if ($secondaryLink) {
            $secondaryLinkArray = $secondaryLink->toArray();
            Assertion::notBlank($secondaryLinkArray['url']);
            $this->secondaryUrl = $secondaryLinkArray['url'];
            $this->secondaryLabel = $secondaryLinkArray['name'];
        }

        $this->secondaryIsActive = $secondaryIsActive;

        if ($isTabSelector) {
            $this->isTabSelector = $isTabSelector;
        }

        $this->sideBySideUrl = $sideBySideUrl;
        $this->otherLinks = $otherLinks;
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/view-selector.mustache';
    }
}
