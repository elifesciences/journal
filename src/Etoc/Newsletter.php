<?php

namespace eLife\Journal\Etoc;

abstract class Newsletter
{
    public function label() : string
    {
        return static::LABEL;
    }

    public function description() : string
    {
        return static::DESCRIPTION;
    }

    public function group() : string
    {
        return static::GROUP;
    }

    public function groupId() : int
    {
        return static::GROUP_ID;
    }

    public function __toString() : string
    {
        return $this->label();
    }
}
