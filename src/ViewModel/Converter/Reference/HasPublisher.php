<?php

namespace eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model;

trait HasPublisher
{
    private function publisherToString(Model\Place $place) : string
    {
        $string = implode(', ', $place->getName());

        if ($place->getAddress()) {
            $string = implode(', ', $place->getAddress()->getFormatted()->toArray()).': '.$string;
        }

        return $string;
    }
}
