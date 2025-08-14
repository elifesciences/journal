<?php

namespace eLife\Patterns;

trait ArrayAccessFromProperties
{
    use ReadOnlyArrayAccess;

    final public function offsetExists($offset) : bool
    {
        if ('_' === substr($offset, 0, 1)) {
            return false;
        }

        return isset($this->{$offset});
    }

    final public function offsetGet($offset)
    {
        if (false === $this->offsetExists($offset)) {
            return null;
        }

        return $this->{$offset};
    }
}
