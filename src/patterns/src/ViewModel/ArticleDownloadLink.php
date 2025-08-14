<?php

namespace eLife\Patterns\ViewModel;

use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\CastsToArray;

final class ArticleDownloadLink implements CastsToArray
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $primary;
    private $secondary;

    public function __construct(Link $primary, Link $secondary = null, string $checkPMC = null)
    {
        $this->primary['link'] = $primary;

        if (null !== $checkPMC) {
            $this->primary['checkPMC'] = $checkPMC;
        }

        $this->secondary = $secondary;
    }
}
