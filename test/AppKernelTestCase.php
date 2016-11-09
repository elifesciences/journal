<?php

namespace test\eLife\Journal;

use eLife\Journal\AppKernel;

trait AppKernelTestCase
{
    final protected static function getKernelClass() : string
    {
        return AppKernel::class;
    }
}
