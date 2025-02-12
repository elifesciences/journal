<?php

namespace test\eLife\Journal\Controller;

use eLife\Journal\Controller\ElifeAssessmentTermsFilter;
use PHPUnit\Framework\TestCase;

final class ElifeAssessmentTermsFilterTest extends TestCase
{
    /**
     * @test
     */
    public function it_translates_a_minimum_significance_of_important_to_a_set_of_filters()
    {
        $result = ElifeAssessmentTermsFilter::fromMinimumSignificance('important');
        $this->markTestSkipped();
        $this->assertSame(['important', 'landmark', 'fundamental'], $result);
    }
}
