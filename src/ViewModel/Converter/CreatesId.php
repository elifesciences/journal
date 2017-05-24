<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\CastsToString;

trait CreatesId
{
    final private function createId(CastsToString $object) : string
    {
        return 'x'.hash('crc32', $object->toString());
    }
}
