<?php

namespace eLife\Patterns;

use ArrayAccess;

interface CastsToArray extends ArrayAccess
{
    public function toArray() : array;
}
