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
    final public function it_builds_significance_terms_when_there_are_significance_terms()
    {
        $builder = new TeaserTermsBuilder();

        $elifeAssessment = new ElifeAssessment(['Landmark'], null);
        $result = $builder->build($elifeAssessment);

        $expected = new TeaserTerms([new Term('Landmark')]);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    final public function it_does_not_build_significance_terms_when_none_are_available()
    {
        $builder = new TeaserTermsBuilder();

        $elifeAssessment = new ElifeAssessment([], null);
        $result = $builder->build($elifeAssessment);

        $this->assertNull($result);
    }

    /**
     * @test
     */
    final public function it_builds_strength_terms_when_there_are_strength_terms()
    {
        $this->markTestIncomplete('incomplete');
    }

    /**
     * @test
     */
    final public function it_does_not_build_strength_terms_when_none_are_available()
    {
        $this->markTestIncomplete('incomplete');
    }

    /**
     * @test
     */
    final public function it_capitalises_the_first_letter_of_the_term()
    {
        $this->markTestIncomplete('incomplete');
    }
}
