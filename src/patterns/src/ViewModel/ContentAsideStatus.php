<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\CastsToArray;

final class ContentAsideStatus implements CastsToArray
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;
    use HasTitleLength;

    private $title;
    private $titleLength;
    private $description;
    private $link;

    public function __construct(string $title, string $description = null, Link $link = null)
    {
        Assertion::notBlank($title);
        Assertion::nullOrNotBlank($description);

        $this->title = $title;
        $this->titleLength = $this->determineTitleLength($this->title, [
            23 => 'short',
            null => 'long',
        ]);
        $this->description = $description;
        $this->link = $link;
    }
}
