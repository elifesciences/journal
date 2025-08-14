<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class EmailCta implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $headerText;
    private $button;
    private $privacyUrl;
    private $privacyLabel;

    public function __construct(
        string $headerText,
        Button $button,
        string $privacyUrl,
        string $privacyLabel
    ) {
        Assertion::notBlank($headerText);
        Assertion::notBlank($privacyUrl);
        Assertion::notBlank($privacyLabel);

        $this->headerText = $headerText;
        $this->button = FlexibleViewModel::fromViewModel($button)
            ->withProperty('classes', "{$button['classes']} email-cta__button");
        $this->privacyUrl = $privacyUrl;
        $this->privacyLabel = $privacyLabel;
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/email-cta.mustache';
    }
}
