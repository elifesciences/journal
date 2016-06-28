<?php

namespace test\eLife\Journal;

use eLife\Journal\AppKernel;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;

abstract class WebTestCase extends BaseWebTestCase
{
    final protected static function getKernelClass()
    {
        return AppKernel::class;
    }
}
