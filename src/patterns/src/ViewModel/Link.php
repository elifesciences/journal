<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\CastsToArray;

final class Link implements CastsToArray
{
    const STYLE_END_OF_GROUP = 'end-of-group';
    const STYLE_HIDDEN_WIDE = 'hidden-wide';

    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $name;
    private $url = null;
    private $ariaLabel;
    private $isCurrent;
    private $attributes;
    private $classes;

    public function __construct(string $name, string $url = null, string $ariaLabel = null, bool $isCurrent = false, array $attributes = [])
    {
        Assertion::notBlank($name);

        $this->name = $name;
        $this->url = $url ?? false;
        $this->ariaLabel = $ariaLabel;
        if ($isCurrent) {
            $this->isCurrent = true;
        }
        $this->attributes = array_reduce(array_keys($attributes), function (array $carry, string $key) use ($attributes) {
            $carry[] = ['key' => $key, 'value' => $attributes[$key]];

            return $carry;
        }, []);
    }

    public function hiddenWide() : self {
        $this->addClass(self::STYLE_HIDDEN_WIDE);

        return $this;
    }

    public function endOfGroup() : self {
        $this->addClass(self::STYLE_END_OF_GROUP);

        return $this;
    }

    private function addClass($class) {
        if (!in_array($class, $this->classes ? explode(' ', $this->classes) : [])) {
            $this->classes = trim($this->classes.' '.$class);
        }
    }
}
