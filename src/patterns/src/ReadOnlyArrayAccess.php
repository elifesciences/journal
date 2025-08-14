<?php

namespace eLife\Patterns;

use BadMethodCallException;

trait ReadOnlyArrayAccess
{
    final public function offsetSet($offset, $value)
    {
        throw new BadMethodCallException('Object is immutable');
    }

    final public function offsetUnset($offset)
    {
        throw new BadMethodCallException('Object is immutable');
    }
}
