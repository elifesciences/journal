<?php

namespace eLife\Journal\Pagerfanta;

use eLife\ApiSdk\Collection\Sequence;
use Pagerfanta\Adapter\AdapterInterface;

final class SequenceAdapter implements AdapterInterface
{
    private $sequence;
    private $map;

    public function __construct(Sequence $sequence, callable $map = null)
    {
        $this->sequence = $sequence;
        $this->map = $map;
    }

    public function getNbResults() : int
    {
        return count($this->sequence);
    }

    public function getSlice($offset, $length) : Sequence
    {
        $slice = $this->sequence->slice($offset, $length);

        if ($this->map) {
            $slice = $slice->map($this->map);
        }

        return $slice;
    }
}
