<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class Picture implements ViewModel, IsCaptioned
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $fallback;
    private $sources;

    public function __construct(array $sources, Image $fallback)
    {
        Assertion::allIsArray($sources);

        $this->sources = $sources;
        $this->fallback = $fallback;
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/picture.mustache';
    }
}
