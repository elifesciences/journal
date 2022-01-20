<?php

namespace eLife\Journal\Etoc;

abstract class Newsletter
{
    private $unsubscribeUrl;

    public function __construct(string $unsubscribeUrl = null)
    {
        $this->unsubscribeUrl = $unsubscribeUrl;
    }

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

    public function unsubscribeField() : string
    {
        return static::UNSUBSCRIBE_FIELD;
    }

    public function unsubscribeUrl() : ?string
    {
        return $this->unsubscribeUrl;
    }

    public function __toString() : string
    {
        return $this->label();
    }
}
