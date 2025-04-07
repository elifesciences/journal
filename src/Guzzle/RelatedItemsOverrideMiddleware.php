<?php

namespace eLife\Journal\Guzzle;

final class RelatedItemsOverrideMiddleware
{
    public function __invoke(callable $handler) : callable
    {
        return $handler;
    }
}
