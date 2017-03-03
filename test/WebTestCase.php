<?php

namespace test\eLife\Journal;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;

abstract class WebTestCase extends BaseWebTestCase
{
    use AppKernelTestCase;
}
