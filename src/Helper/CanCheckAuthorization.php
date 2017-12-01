<?php

namespace eLife\Journal\Helper;

trait CanCheckAuthorization
{
    use HasAuthorizationChecker;

    final protected function ifGranted(array $attributes, callable $ifGranted) : callable
    {
        return function ($object) use ($attributes, $ifGranted) {
            if (!$this->isGranted(...$attributes)) {
                return null;
            }

            return $ifGranted($object);
        };
    }

    final protected function isGranted(string ...$attributes) : bool
    {
        return $this->getAuthorizationChecker()->isGranted($attributes);
    }
}
