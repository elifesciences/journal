<?php

namespace eLife\Journal\Helper;

use Symfony\Component\HttpKernel\UriSigner;

trait HasUriSigner
{
    abstract protected function getUriSigner() : UriSigner;
}
