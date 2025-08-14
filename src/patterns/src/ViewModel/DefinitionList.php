<?php

namespace eLife\Patterns\ViewModel;

use function array_values;
use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class DefinitionList implements ViewModel
{
    const COLOR_VOR = 'vor';

    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $items;
    private $variant;
    private $color;
    private $label;
    private $isActive;

    private function __construct(array $items, string $variant = null, string $color = null, string $label = null, bool $isActive = false)
    {
        Assertion::notEmpty($items);
        Assertion::allNotEmpty($items);
        Assertion::nullOrChoice($color, [self::COLOR_VOR]);

        if ('timeline' === $variant) {
            $this->items = $items;
            $this->color = $color;
            if ($isActive) {
                $this->isActive = $isActive;
            }
        } else {
            $this->items = array_map(function (string $term, $descriptors) {
                $descriptors = (array)$descriptors;

                Assertion::allString($descriptors);

                return [
                    'term' => $term,
                    'descriptors' => $descriptors,
                ];
            }, array_keys($items), array_values($items));
        }

        $this->variant = $variant;
        $this->label = $label;
    }

    public static function basic(array $items) : DefinitionList
    {
        return new self($items);
    }

    public static function inline(array $items) : DefinitionList
    {
        return new self($items, 'inline');
    }

    public static function timeline(array $items, string $color = null, string $label = null, bool $isActive = false) : DefinitionList
    {
        return new self($items, 'timeline', $color, $label, $isActive);
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/definition-list.mustache';
    }
}
