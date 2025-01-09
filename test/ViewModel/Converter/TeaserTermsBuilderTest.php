<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\ElifeAssessment;
use eLife\Journal\ViewModel\Converter\TeaserTermsBuilder;
use eLife\Patterns\ViewModel\TeaserTerms;
use eLife\Patterns\ViewModel\Term;
use PHPUnit\Framework\TestCase;

final class TeaserTermsBuilderTest extends TestCase
{
    /**
     * @var TeaserTermsBuilder
     */
    private $builder;

     /**
     * @before
     */
    public function setUpConverter()
    {
        $this->builder = new TeaserTermsBuilder();
    }

    /**
     * @test
     */
    final public function it_builds_significance_terms_when_there_are_significance_terms_and_no_strength_terms_are_available()
    {
        $elifeAssessment = new ElifeAssessment(['landmark'], null);
        $result = $this->builder->build($elifeAssessment);

        $expected = new TeaserTerms([new Term('Landmark')]);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    final public function it_does_not_build_significance_terms_when_there_are_none_in_the_assessment_and_no_strength_terms_are_available()
    {
        $elifeAssessment = new ElifeAssessment([], null);
        $result = $this->builder->build($elifeAssessment);

        $this->assertNull($result);
    }

    /**
     * @test
     */
    final public function it_builds_strength_terms_when_there_are_strength_terms_and_no_significance_terms_are_available()
    {
        $elifeAssessment = new ElifeAssessment(null, ['convincing']);
        $result = $this->builder->build($elifeAssessment);

        $expected = new TeaserTerms([new Term('Convincing')]);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    final public function it_builds_terms_using_both_significance_and_strength_terms_in_that_order()
    {
        $elifeAssessment = new ElifeAssessment(['landmark'], ['solid']);
        $result = $this->builder->build($elifeAssessment);

        $expected = new TeaserTerms([new Term('Landmark'), new Term('Solid')]);
        $this->assertEquals($expected, $result);
    }
}
