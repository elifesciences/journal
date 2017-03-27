<?php

namespace eLife\Journal\Mustache;

use Mustache_LambdaHelper;
use Symfony\Component\Asset\Packages;

final class PackagesHelper
{
    private $packages;

    public function __construct(Packages $packages)
    {
        $this->packages = $packages;
    }

    public function __invoke(string $path, Mustache_LambdaHelper $helper) : string
    {
        return $this->packages->getUrl($helper->render($path));
    }
}
