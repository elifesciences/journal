<?php

namespace test\eLife\Journal\Mustache;

use eLife\Journal\Mustache\PackagesHelper;
use Mustache_Context;
use Mustache_Engine;
use Mustache_LambdaHelper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Asset\PathPackage;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;

final class PackagesHelperTest extends TestCase
{
    private $packages;
    private $lambdaHelper;

    /**
     * @before
     */
    public function setUpPackages()
    {
        $this->packages = new Packages(new PathPackage('/foo', new EmptyVersionStrategy()));
    }

    /**
     * @before
     */
    public function setUpLambdaHelper()
    {
        $this->lambdaHelper = new Mustache_LambdaHelper(new Mustache_Engine(), new Mustache_Context(['bar' => 'baz']));
    }

    /**
     * @test
     */
    public function it_rewrites_paths()
    {
        $helper = new PackagesHelper($this->packages);

        $this->assertEquals('/foo/baz.qux', $helper('{{bar}}.qux', $this->lambdaHelper));
    }
}
