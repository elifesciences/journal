<?php

namespace test\eLife\Journal\Controller;

use eLife\Journal\Controller\ElifeAssessmentTermsFilter;
use PHPUnit\Framework\TestCase;
use Traversable;

final class ElifeAssessmentTermsFilterTest extends TestCase
{
    public function significanceProvider() : Traversable
    {
        yield 'important' => [
            'important',
            ['important', 'fundamental', 'landmark'],
        ];
    }

    /**
     * @test
     * @dataProvider significanceProvider
     */
    public function it_translates_a_minimum_significance_to_the_correct_set_of_filters(string $input, array $expected)
    {
        $result = ElifeAssessmentTermsFilter::fromMinimumSignificance($input);
        $this->assertEqualsCanonicalizing($expected, $result);
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
