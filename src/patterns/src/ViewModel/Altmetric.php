<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class Altmetric implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $doi;
    private $donutSize;
    private $showBadgeDetails;

    public function __construct(
        string $doi,
        string $donutSize,
        bool $showBadgeDetails
    ) {
        Assertion::notBlank($doi);
        Assertion::notBlank($donutSize);
        Assertion::notNull($showBadgeDetails);

        $this->doi = $doi;
        $this->donutSize = $donutSize;
        $this->showBadgeDetails = $showBadgeDetails;
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/altmetric.mustache';
    }
}
