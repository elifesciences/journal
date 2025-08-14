<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\CastsToArray;

final class MediaSourceFallback implements CastsToArray
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $content;
    private $isExternal;

    public function __construct(string $content, bool $isExternal = false)
    {
        Assertion::notBlank($content);

        $this->content = $content;
        $this->isExternal = $isExternal;
    }
}
