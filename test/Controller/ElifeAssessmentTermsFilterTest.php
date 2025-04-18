<?php

namespace test\eLife\Journal\Controller;

use eLife\Journal\Controller\ElifeAssessmentTermsFilter;
use PHPUnit\Framework\TestCase;
use Traversable;

final class ElifeAssessmentTermsFilterTest extends TestCase
{
    public function significanceProvider() : Traversable
    {
        yield 'landmark' => [
            ['landmark'],
            'landmark',
        ];
        yield 'fundamental' => [
            ['landmark', 'fundamental'],
            'fundamental',
        ];
        yield 'important' => [
            ['important', 'fundamental', 'landmark'],
            'important',
        ];
        yield 'valuable' => [
            ['important', 'fundamental', 'landmark', 'valuable'],
            'valuable',
        ];
        yield 'valuableAndInclusionOfOriginalModelPapers' => [
            ['important', 'fundamental', 'landmark', 'valuable', 'not-applicable'],
            'valuable',
            'yes',
        ];
        yield 'useful' => [
            ['important', 'fundamental', 'landmark', 'valuable', 'useful'],
            'useful',
        ];
        yield 'noMinimumSignificance' => [
            ['important', 'fundamental', 'landmark', 'useful', 'valuable', 'not-assigned'],
            null,
        ];
        yield 'emptyMinimumSignificance' => [
            ['important', 'fundamental', 'landmark', 'useful', 'valuable', 'not-assigned'],
            '',
        ];
        yield 'emptyMinimumSignificanceAndInclusionOfOriginalModelPapers' => [
            ['important', 'fundamental', 'landmark', 'useful', 'valuable', 'not-assigned', 'not-applicable'],
            '',
            'yes',
        ];
        yield 'notASignificanceTerm' => [
            ['important', 'fundamental', 'landmark', 'useful', 'valuable', 'not-assigned'],
            'notASignificanceTerm',
        ];
    }

    public function strengthProvider() : Traversable
    {
        yield 'exceptional' => [
            ['exceptional'],
            'exceptional',
        ];
        yield 'compelling' => [
           ['exceptional', 'compelling'],
           'compelling',
        ];
        yield 'convincing' => [
           ['exceptional', 'compelling', 'convincing'],
           'convincing',
        ];
        yield 'convincingAndInclusionOfOriginalModelPapers' => [
            ['exceptional', 'compelling', 'convincing', 'not-applicable'],
            'convincing',
            'yes',
        ];
        yield 'solid' => [
           ['exceptional', 'compelling', 'convincing', 'solid'],
           'solid',
        ];
        yield 'incomplete' => [
            ['exceptional', 'compelling', 'convincing', 'solid', 'incomplete'],
            'incomplete',
        ];
        yield 'inadequate' => [
            ['exceptional', 'compelling', 'convincing', 'solid', 'incomplete', 'inadequate'],
            'inadequate',
        ];
        yield 'emptyMinimumStrength' => [
            ['exceptional', 'compelling', 'convincing', 'solid', 'incomplete', 'inadequate'],
            '',
        ];
        yield 'emptyMinimumStrengthAndInclusionOfOriginalModelPapers' => [
           ['exceptional', 'compelling', 'convincing', 'solid', 'incomplete', 'inadequate', 'not-applicable'],
           '',
           'yes',
        ];
        yield 'notAStrengthTerm' => [
            ['exceptional', 'compelling', 'convincing', 'solid', 'incomplete', 'inadequate'],
            'notAStrengthTerm',
        ];
    }

    /**
     * @test
     * @dataProvider significanceProvider
     */
    public function it_translates_a_minimum_significance_to_the_correct_set_of_filters(array $expected, string $input = null, string $includeOriginalModelPapers = '')
    {
        $query = $this->overrideDefaultQueryStringWith(['includeOriginalModelPapers' => $includeOriginalModelPapers]);
        $result = ElifeAssessmentTermsFilter::fromMinimumSignificance($input, $query);
        $this->assertEqualsCanonicalizing($expected, $result);
    }

    /**
     * @test
     * @dataProvider strengthProvider
     */
    public function it_translates_a_minimum_strength_to_the_correct_set_of_filters(array $expected, string $input = null, string $includeOriginalModelPapers = '')
    {
        $query = $this->overrideDefaultQueryStringWith(['includeOriginalModelPapers' => $includeOriginalModelPapers]);
        $result = ElifeAssessmentTermsFilter::fromMinimumStrength($input, $query);
        $this->assertEqualsCanonicalizing($expected, $result);
    }

    /**
     * @test
     */
    public function it_includes_original_model_papers_when_the_query_string_contains_nothing()
    {
        $query = $this->overrideDefaultQueryStringWith([]);
        $this->assertTrue(ElifeAssessmentTermsFilter::decideWhetherToIncludeOriginalModelPapers($query));
    }

    /**
     * @test
     */
    public function it_includes_original_model_papers_when_the_query_string_contains_include_original_papers_with_the_yes_value()
    {
        $query = $this->overrideDefaultQueryStringWith(['includeOriginalModelPapers' => 'yes']);
        $this->assertTrue(ElifeAssessmentTermsFilter::decideWhetherToIncludeOriginalModelPapers($query));
    }

    /**
     * @test
     */
    public function it_does_not_include_original_model_papers_when_the_query_string_contains_include_original_papers_with_any_value_that_is_not_yes()
    {
        $query = $this->overrideDefaultQueryStringWith(['includeOriginalModelPapers' => 'not yes']);
        $this->assertFalse(ElifeAssessmentTermsFilter::decideWhetherToIncludeOriginalModelPapers($query));
    }

    /**
     * @test
     */
    public function it_does_not_include_original_model_papers_when_the_query_string_contains_minimum_significance_and_include_original_papers_with_any_value_that_is_not_yes()
    {
        $query = $this->overrideDefaultQueryStringWith(['minimumSignificance' => 'valuable', 'includeOriginalModelPapers' => 'not yes']);
        $this->assertFalse(ElifeAssessmentTermsFilter::decideWhetherToIncludeOriginalModelPapers($query));
    }

    /**
     * @test
     */
    public function it_includes_original_model_papers_when_the_query_string_contains_minimum_strength_and_include_original_papers_with_the_yes_value()
    {
        $query = $this->overrideDefaultQueryStringWith(['minimumStrength' => 'convincing', 'includeOriginalModelPapers' => 'yes']);
        $this->assertTrue(ElifeAssessmentTermsFilter::decideWhetherToIncludeOriginalModelPapers($query));
    }

    private function overrideDefaultQueryStringWith(array $specifiedParameters = []): array
    {
        return array_merge(
            [
                'subjects' => [],
                'types' => [],
                'minimumSignificance' => null,
                'minimumStrength' => null,
                'includeOriginalModelPapers' => null,
            ],
            $specifiedParameters
        );
    }
}
