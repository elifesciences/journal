<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\CastsToArray;

final class SortControlOption implements CastsToArray
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    const ASC = 'ascending';
    const DESC = 'descending';

    private $link;
    private $sorting;

    public function __construct(Link $link, string $sorting = null)
    {
        Assertion::nullOrInArray($sorting, [self::ASC, self::DESC]);

        $this->link = $link;
        $this->sorting = $sorting;
    }
}
