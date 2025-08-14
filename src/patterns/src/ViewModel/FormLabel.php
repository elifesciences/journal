<?php

namespace eLife\Patterns\ViewModel;

use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\CastsToArray;

final class FormLabel implements CastsToArray
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $labelText;
    private $isVisuallyHidden;

    public function __construct(string $labelText, bool $isVisuallyHidden = false)
    {
        $this->labelText = $labelText;
        $this->isVisuallyHidden = $isVisuallyHidden;
    }
}
