<?php

namespace eLife\Journal;

class FilterBlocksOnClass
{
    private $class;

    public static function for($class)
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
