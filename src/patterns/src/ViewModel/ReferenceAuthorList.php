<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\CastsToArray;

final class ReferenceAuthorList implements CastsToArray
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $authors;
    private $suffix;

    public function __construct(array $authors, string $suffix)
    {
        Assertion::notEmpty($authors);
        Assertion::allIsInstanceOf($authors, Author::class);
        Assertion::notBlank($suffix);

        $this->authors = $authors;
        $this->suffix = $suffix;
    }
}
