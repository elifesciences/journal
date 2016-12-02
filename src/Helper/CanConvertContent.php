<?php

namespace eLife\Journal\Helper;

use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\HasContent;

trait CanConvertContent
{
    use CanConvert;

    final protected function willConvertContent(int $level = 2): callable
    {
        return function (HasContent $object) use ($level) {
            return $this->convertContent($object, $level);
        };
    }

    final protected function convertContent(HasContent $object, int $level = 2): Sequence
    {
        return $object->getContent()->map($this->willConvertTo(null, ['level' => $level]));
    }
}
