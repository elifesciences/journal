<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\CastsToArray;

final class Image implements CastsToArray, IsCaptioned
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $altText;
    private $defaultPath;
    private $srcset;
    private $sizes;

    public function __construct(string $defaultPath, array $srcset = [], string $altText = '', string $sizes = null)
    {
        Assertion::notBlank($defaultPath);
        Assertion::allNumeric(array_keys($srcset));
        if (!empty($srcset)) {
            Assertion::inArray(1, array_keys($srcset));
        }
        Assertion::allNotBlank($srcset);

        $this->defaultPath = $defaultPath;
        $this->srcset = [];
        if ($srcset) {
            foreach ($srcset as $dpiMultiple => $src) {
                $this->srcset[] = $src.' '.$dpiMultiple.'x';
            }
            $this->srcset = implode(', ', $this->srcset);
        }
        $this->altText = $altText;
        $this->sizes = $sizes;
    }
}
