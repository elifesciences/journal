<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class AuthorsDetails implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $authorDetails;

    public function __construct(AuthorDetails ...$authorDetails)
    {
        Assertion::notBlank($authorDetails);

        $this->authorDetails = $authorDetails;
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/authors-details.mustache';
    }
}
