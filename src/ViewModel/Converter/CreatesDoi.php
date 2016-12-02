<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\HasDoi;
use eLife\Patterns\ViewModel;

trait CreatesDoi
{
    /**
     * @return ViewModel\Doi|null
     */
    final private function createDoi(HasDoi $object)
    {
        return $object->getDoi() ? new ViewModel\Doi($object->getDoi()) : null;
    }
}
