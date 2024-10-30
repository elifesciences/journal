<?php

namespace test\eLife\Journal\Helper;

use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Model\ArticleSection;
use eLife\Journal\Helper\CanBuildAssessment;
use PHPUnit\Framework\TestCase;
 
class CanBuildAssessmentTest extends TestCase
{
    public function testReturnStateAnonymous(): void
    {
        $controller = new class {
            use CanBuildAssessment;
        };
        $content = new EmptySequence();
        $elifeAssessment = new ArticleSection($content);
        $result = $controller->buildAssessmentViewModel($elifeAssessment);
        $this->markTestIncomplete();
    }
}
