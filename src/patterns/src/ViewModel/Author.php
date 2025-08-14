<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\CastsToArray;

final class Author implements CastsToArray
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $name;
    private $url;
    private $isCorresponding;

    private function __construct(string $name, string $url = null, bool $isCorresponding = false)
    {
        Assertion::notBlank($name);

        $this->name = $name;
        $this->url = $url ?? false;
        if ($isCorresponding) {
            $this->isCorresponding = true;
        }
    }

    public static function asText(string $name, bool $isCorresponding = false)
    {
        return new static($name, null, $isCorresponding);
    }

    public static function asLink(Link $link, bool $isCorresponding = false)
    {
        return new static(
            $link['name'],
            $link['url'],
            $isCorresponding
        );
    }
}
