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
        $this->assertContains('important', $result);
        $this->assertContains('landmark', $result);
        $this->assertContains('fundamental', $result);
        $this->assertCount(3, $result);
    }

    /**
     * @test
     */
    public function it_translates_no_minimum_significance_to_a_complete_set_of_filters()
    {
        $result = ElifeAssessmentTermsFilter::fromMinimumSignificance();
        $this->assertContains('important', $result);
        $this->assertContains('landmark', $result);
        $this->assertContains('fundamental', $result);
        $this->assertContains('valuable', $result);
        $this->assertContains('useful', $result);
        $this->assertCount(5, $result);
    }

    /**
     * @test
     */
    public function it_translates_empty_minimum_significance_to_a_complete_set_of_filters()
    {
        $result = ElifeAssessmentTermsFilter::fromMinimumSignificance('');
        $this->assertContains('important', $result);
        $this->assertContains('landmark', $result);
        $this->assertContains('fundamental', $result);
        $this->assertContains('valuable', $result);
        $this->assertContains('useful', $result);
        $this->assertCount(5, $result);
    }
}
