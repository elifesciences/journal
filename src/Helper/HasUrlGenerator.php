<?php

namespace eLife\Journal\Helper;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

trait HasUrlGenerator
{
    abstract protected function getUrlGenerator() : UrlGeneratorInterface;
}
