<?php

namespace test\eLife\Journal\Helper;

use eLife\Journal\Helper\CanBuildAssessment;
use PHPUnit\Framework\TestCase;
 
class CanBuildAssessmentTest extends TestCase
{
    public function testReturnStateAnonymous(): void
    {
        $controller = new class {
            use CanBuildAssessment;
        };
    }
}
