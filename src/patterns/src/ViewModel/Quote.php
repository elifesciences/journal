<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class Quote implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $quote;
    private $cite;

    public function __construct(
        string $quote,
        string $cite = null
    ) {
        Assertion::notBlank($quote);

        $this->quote = $quote;
        $this->cite = $cite;
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/quote.mustache';
    }
}
