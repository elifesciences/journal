<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Model\Block;

final class FilterBlocks
{
    private $class;

    public static function byClass($class)
    {
        return new self($class);
    }

    private function __construct($class)
    {
        $this->class = $class;
    }

    public function __invoke(array $objects) : array
    {
        return array_filter($objects, function (Block $block) {
            return $block instanceof $this->class;
        });
    }
}
