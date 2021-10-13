<?php

namespace eLife\Journal\Exception;

use RuntimeException;
use Throwable;

class FormSuccess extends RuntimeException
{
    public function __construct(string $message, Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
