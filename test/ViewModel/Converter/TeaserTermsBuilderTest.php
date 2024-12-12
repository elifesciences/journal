<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\ElifeAssessment;
use eLife\Journal\ViewModel\Converter\ReviewedPreprintTeaserConverter;
use eLife\Journal\ViewModel\Converter\TeaserTermsBuilder;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\TeaserTerms;
use eLife\Patterns\ViewModel\Term;
use PHPUnit\Framework\TestCase;

final class TeaserTermsBuilderTest extends TestCase
{
    /**
     * @test
     */
    final public function it_builds_significance_terms_when_there_are_significance_terms_and_no_strength_terms_are_available()
    {
        $builder = new TeaserTermsBuilder();

        $elifeAssessment = new ElifeAssessment(['landmark'], null);
        $result = $builder->build($elifeAssessment);

        $expected = new TeaserTerms([new Term('Landmark')]);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    final public function it_does_not_build_significance_terms_when_there_are_none_in_the_assessment_and_no_strength_terms_are_available()
    {
        $builder = new TeaserTermsBuilder();

        $elifeAssessment = new ElifeAssessment([], null);
        $result = $builder->build($elifeAssessment);

        $this->assertNull($result);
    }

    /**
     * @test
     */
    final public function it_builds_strength_terms_when_there_are_strength_terms_and_no_significance_terms_are_available()
    {
        $builder = new TeaserTermsBuilder();

        $elifeAssessment = new ElifeAssessment(null, ['convincing']);
        $result = $builder->build($elifeAssessment);

        $expected = new TeaserTerms([new Term('Convincing')]);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    final public function it_builds_terms_using_both_strength_and_significance_terms()
    {
        $this->markTestIncomplete('incomplete');
    }
}
