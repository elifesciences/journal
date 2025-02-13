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
            ['important', 'fundamental', 'landmark'],
            'important',
        ];
        yield 'valuable' => [
            ['important', 'fundamental', 'landmark', 'valuable'],
            'valuable',
        ];
        yield 'noMinimumSignificance' => [
            ['important', 'fundamental', 'landmark', 'useful', 'valuable'],
            null,
        ];
        yield 'emptyMinimumSignificance' => [
            ['important', 'fundamental', 'landmark', 'useful', 'valuable'],
            '',
        ];
    }

    /**
     * @test
     * @dataProvider significanceProvider
     */
    public function it_translates_a_minimum_significance_to_the_correct_set_of_filters(array $expected, string $input = null)
    {
        $result = ElifeAssessmentTermsFilter::fromMinimumSignificance($input);
        $this->assertEqualsCanonicalizing($expected, $result);
    }
}
