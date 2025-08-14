<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class InvestorLogos implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $logos;

    public function __construct(Picture ...$logos)
    {
        Assertion::notEmpty($logos);

        $this->logos = $logos;
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/investor-logos.mustache';
    }
}
