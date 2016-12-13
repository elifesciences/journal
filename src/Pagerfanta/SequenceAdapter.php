<?php

namespace eLife\Journal\Pagerfanta;

use eLife\ApiSdk\Collection\Sequence;
use Pagerfanta\Adapter\AdapterInterface;

final class SequenceAdapter implements AdapterInterface
{
    private $sequence;

    public function __construct(Sequence $sequence)
    {
        $this->sequence = $sequence;
    }

    public function getNbResults() : int
    {
        return count($this->sequence);
    }

    public function getSlice($offset, $length) : Sequence
    {
        return $this->sequence->slice($offset, $length);
    }
}
