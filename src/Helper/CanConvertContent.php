<?php

namespace eLife\Journal\Helper;

use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\HasContent;

trait CanConvertContent
{
    use CanConvert;

    final protected function willConvertContent(int $level = 2, array $context = []) : callable
    {
        return function (HasContent $object) use ($level, $context) {
            return $this->convertContent($object, $level, $context);
        };
    }

    final protected function convertContent(HasContent $object, int $level = 2, array $context = []) : Sequence
    {
        return $object->getContent()->map($this->willConvertTo(null, $context + ['level' => $level]));
    }
}
