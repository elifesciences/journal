<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class ProcessBlock implements ViewModel
{
    const COLOR_VOR = 'vor';

    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $body;
    private $variant;
    private $link;

    public function __construct(string $body, string $variant = null, Link $link = null)
    {
        Assertion::nullOrChoice($variant, [self::COLOR_VOR]);

        $this->body = $body;
        $this->variant = $variant;
        $this->link = $link;
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/process-block.mustache';
    }
}
