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
        $this->assertEqualsCanonicalizing(['important', 'fundamental', 'landmark'], $result);
    }

    /**
     * @test
     */
    public function it_translates_a_minimum_significance_of_valuable_to_a_set_of_filters()
    {
        $result = ElifeAssessmentTermsFilter::fromMinimumSignificance('valuable');
        $this->assertEqualsCanonicalizing(['important', 'fundamental', 'landmark', 'valuable'], $result);
    }

    /**
     * @test
     */
    public function it_translates_no_minimum_significance_to_a_complete_set_of_filters()
    {
        $result = ElifeAssessmentTermsFilter::fromMinimumSignificance();
        $this->assertEqualsCanonicalizing(['important', 'fundamental', 'landmark', 'useful', 'valuable'], $result);
    }

    /**
     * @test
     */
    public function it_translates_empty_minimum_significance_to_a_complete_set_of_filters()
    {
        $result = ElifeAssessmentTermsFilter::fromMinimumSignificance('');
        $this->assertEqualsCanonicalizing(['important', 'fundamental', 'landmark', 'useful', 'valuable'], $result);
    }
}
