<?php

namespace eLife\Journal\Etoc;

abstract class NewsLetter
{
    public function label() : string
    {
        return static::LABEL;
    }

    public function group() : string
    {
        return static::GROUP;
    }

    public function groupId() : int
    {
        return static::GROUP_ID;
    }

    /**
     * @return string|null
     */
    public function unsubscribeUrl()
    {
        return $this->unsubscribeUrl;
    }

    public function __toString()
    {
        return $this->label();
    }
}
