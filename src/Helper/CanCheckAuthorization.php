<?php

namespace eLife\Journal\Helper;

trait CanCheckAuthorization
{
    use HasAuthorizationChecker;

    final protected function isGranted(mixed $attribute, mixed $subject = null) : bool
    {
        if ($this->getAuthorizationChecker()->isGranted($attribute, $subject)) {
            return true;
        }

        return false;
    }
}
