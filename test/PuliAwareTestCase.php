<?php

namespace test\eLife\Journal;

use Puli\Repository\Api\ResourceRepository;

trait PuliAwareTestCase
{
    /**
     * @var ResourceRepository
     */
    private static $puli;

    /**
     * @beforeClass
     */
    final public static function setUpPuli()
    {
        $factoryClass = PULI_FACTORY_CLASS;
        self::$puli = (new $factoryClass())->createRepository();
    }
}
